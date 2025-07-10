<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'original_name',
        'file_path',
        'mime_type',
        'size',
        'description',
        'is_public',
        'category',
        'metadata',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'metadata' => 'array',
        'size' => 'integer',
    ];

    /**
     * リソースのアクセス制御
     */
    public function accessControls(): HasMany
    {
        return $this->hasMany(ResourceAccessControl::class);
    }

    /**
     * アクセスログ
     */
    public function accessLogs(): HasMany
    {
        return $this->hasMany(ResourceAccessLog::class);
    }

    /**
     * アクティブなアクセス制御のみ
     */
    public function activeAccessControls(): HasMany
    {
        return $this->accessControls()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * ファイルサイズを人間が読める形式で取得
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * ファイルの拡張子を取得
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->original_name, PATHINFO_EXTENSION);
    }

    /**
     * ファイルが画像かどうか
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * ファイルの物理パスを取得
     */
    public function getFullPathAttribute(): string
    {
        return Storage::path($this->file_path);
    }

    /**
     * ファイルが存在するかチェック
     */
    public function fileExists(): bool
    {
        return Storage::exists($this->file_path);
    }

    /**
     * IP アドレスがアクセス許可されているかチェック
     */
    public function isIpAllowed(string $ip): bool
    {
        if ($this->is_public) {
            return true;
        }

        $ipControls = $this->activeAccessControls()
            ->where('type', 'ip_whitelist')
            ->get();

        if ($ipControls->isEmpty()) {
            return false;
        }

        return $ipControls->contains('value', $ip);
    }

    /**
     * トークンが必要かチェック
     */
    public function requiresToken(): bool
    {
        return $this->activeAccessControls()
            ->where('type', 'token_required')
            ->exists();
    }

    /**
     * カテゴリの選択肢
     */
    public static function getCategories(): array
    {
        return [
            'document' => 'ドキュメント',
            'image' => '画像',
            'video' => '動画',
            'audio' => '音声',
            'archive' => 'アーカイブ',
            'other' => 'その他',
        ];
    }

    /**
     * 検索スコープ
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('original_name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * カテゴリでフィルタ
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * 公開状態でフィルタ
     */
    public function scopePublic($query, $isPublic = true)
    {
        return $query->where('is_public', $isPublic);
    }

    /**
     * CSV出力用のヘッダー
     */
    public static function getCsvHeaders(): array
    {
        return [
            'ID',
            'リソース名',
            'ファイル名',
            'MIMEタイプ',
            'サイズ',
            'カテゴリ',
            '公開状態',
            '説明',
            '登録日',
        ];
    }

    /**
     * CSV出力用のデータ配列
     */
    public function toCsvArray(): array
    {
        return [
            $this->id,
            $this->name,
            $this->original_name,
            $this->mime_type,
            $this->formatted_size,
            $this->category ? self::getCategories()[$this->category] : '',
            $this->is_public ? '公開' : '非公開',
            $this->description ?? '',
            $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}