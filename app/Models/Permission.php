<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'url',
        'description',
        'remarks',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * この権限を持つロール
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    /**
     * 権限名による検索
     */
    public static function findByName(string $name): ?Permission
    {
        return static::where('name', $name)->first();
    }

    /**
     * アクティブな権限のみを取得
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * URL順で並び替え
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('url')->orderBy('name');
    }

    /**
     * URLパターンによる検索
     */
    public static function findByUrl(string $url): ?Permission
    {
        return static::where('url', $url)->where('is_active', true)->first();
    }

    /**
     * URLパターンマッチング（ワイルドカード対応）
     */
    public static function findByUrlPattern(string $currentUrl): ?Permission
    {
        $permissions = static::active()->get();
        
        foreach ($permissions as $permission) {
            if (static::urlMatches($permission->url, $currentUrl)) {
                return $permission;
            }
        }
        
        return null;
    }

    /**
     * URLパターンマッチング
     */
    private static function urlMatches(string $pattern, string $url): bool
    {
        // 完全一致
        if ($pattern === $url) {
            return true;
        }
        
        // ワイルドカード対応（* を使用）
        if (str_contains($pattern, '*')) {
            $pattern = str_replace('*', '.*', preg_quote($pattern, '/'));
            return preg_match('/^' . $pattern . '$/', $url);
        }
        
        // プレフィックスマッチ（パターンがスラッシュで終わる場合）
        if (str_ends_with($pattern, '/') && str_starts_with($url, $pattern)) {
            return true;
        }
        
        return false;
    }
}