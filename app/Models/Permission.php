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
        'category',
        'sort_order',
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
     * カテゴリと順序で並び替え
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('category')->orderBy('sort_order')->orderBy('name');
    }

    /**
     * 利用可能なカテゴリを取得
     */
    public static function getCategories(): array
    {
        return [
            'admin' => '管理画面',
            'dashboard' => 'ダッシュボード',
            'auth' => '認証',
            'api' => 'API',
            'guide' => 'ガイド',
            'other' => 'その他'
        ];
    }

    /**
     * カテゴリの表示名を取得
     */
    public function getCategoryDisplayNameAttribute(): string
    {
        $categories = static::getCategories();
        return $categories[$this->category] ?? $this->category;
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
     * 最も具体的な（最長の）URLパターンを優先
     */
    public static function findByUrlPattern(string $currentUrl): ?Permission
    {
        $permissions = static::active()->get();
        $matchedPermissions = [];
        
        // マッチする全ての権限を収集
        foreach ($permissions as $permission) {
            if (static::urlMatches($permission->url, $currentUrl)) {
                $matchedPermissions[] = $permission;
            }
        }
        
        if (empty($matchedPermissions)) {
            return null;
        }
        
        // 最も具体的な権限を選択（URLパターンの長さで判定）
        usort($matchedPermissions, function ($a, $b) {
            // 完全一致を最優先
            if ($a->url === $b->url) {
                return 0;
            }
            
            // ワイルドカードなしの完全一致を優先
            $aHasWildcard = str_contains($a->url, '*');
            $bHasWildcard = str_contains($b->url, '*');
            
            if (!$aHasWildcard && $bHasWildcard) {
                return -1;
            }
            if ($aHasWildcard && !$bHasWildcard) {
                return 1;
            }
            
            // 両方ともワイルドカードありの場合、より長い（具体的な）パターンを優先
            $aLength = strlen(str_replace('*', '', $a->url));
            $bLength = strlen(str_replace('*', '', $b->url));
            
            return $bLength <=> $aLength; // 降順（長い方が先）
        });
        
        return $matchedPermissions[0];
    }

    /**
     * URLパターンマッチングのテスト用メソッド
     */
    public static function testUrlMatching(string $pattern, string $url): array
    {
        $result = static::urlMatches($pattern, $url);
        
        return [
            'pattern' => $pattern,
            'url' => $url,
            'matches' => $result,
            'debug' => [
                'exact_match' => $pattern === $url,
                'has_wildcard' => str_contains($pattern, '*'),
                'prefix_match' => str_ends_with($pattern, '/') && str_starts_with($url, $pattern)
            ]
        ];
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
            // ワイルドカードを一時的な文字列に置換
            $escapedPattern = str_replace('*', '__WILDCARD__', $pattern);
            // 正規表現用にエスケープ
            $escapedPattern = preg_quote($escapedPattern, '/');
            // ワイルドカードを正規表現の .* に戻す
            $escapedPattern = str_replace('__WILDCARD__', '.*', $escapedPattern);
            
            return preg_match('/^' . $escapedPattern . '$/', $url) === 1;
        }
        
        // プレフィックスマッチ（パターンがスラッシュで終わる場合）
        if (str_ends_with($pattern, '/') && str_starts_with($url, $pattern)) {
            return true;
        }
        
        return false;
    }
}