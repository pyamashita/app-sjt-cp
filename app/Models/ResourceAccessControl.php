<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceAccessControl extends Model
{
    use HasFactory;

    protected $fillable = [
        'resource_id',
        'type',
        'value',
        'is_active',
        'expires_at',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * リソースとのリレーション
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    /**
     * APIトークンとのリレーション（typeがapi_tokenの場合）
     */
    public function apiToken(): BelongsTo
    {
        return $this->belongsTo(ApiToken::class, 'value');
    }

    /**
     * アクセス制御タイプの選択肢
     */
    public static function getTypes(): array
    {
        return [
            'ip_whitelist' => 'IP許可リスト',
            'api_token' => 'APIトークン',
            'token_required' => 'トークン必須',
            'time_limited' => '時間制限',
        ];
    }

    /**
     * アクセス制御タイプのラベル
     */
    public function getTypeLabelAttribute(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    /**
     * アクティブかどうか（期限も考慮）
     */
    public function getIsActiveNowAttribute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * 期限切れかどうか
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * IPアドレスが有効かチェック
     */
    public function isValidIp(): bool
    {
        if ($this->type !== 'ip_whitelist') {
            return true;
        }

        return filter_var($this->value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * アクティブなアクセス制御のみ
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * 特定のタイプでフィルタ
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * 期限切れのアクセス制御
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }
}