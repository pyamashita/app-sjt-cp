<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'token',
        'permissions',
        'allowed_ips',
        'is_active',
        'expires_at',
        'last_used_at',
        'description',
    ];

    protected $casts = [
        'permissions' => 'array',
        'allowed_ips' => 'array',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = [
        'token',
    ];

    /**
     * アクセスログ
     */
    public function accessLogs(): HasMany
    {
        return $this->hasMany(ResourceAccessLog::class);
    }

    /**
     * 新しいトークンを生成
     */
    public static function generateToken(): string
    {
        return Str::random(64);
    }

    /**
     * 許可されている権限の選択肢
     */
    public static function getPermissions(): array
    {
        return [
            'read' => '読み取り',
            'write' => '書き込み',
            'delete' => '削除',
            'manage' => '管理',
        ];
    }

    /**
     * 権限のラベル
     */
    public function getPermissionLabelsAttribute(): array
    {
        if (!$this->permissions) {
            return [];
        }

        $permissionLabels = self::getPermissions();
        return array_map(function ($permission) use ($permissionLabels) {
            return $permissionLabels[$permission] ?? $permission;
        }, $this->permissions);
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
     * 特定の権限を持っているかチェック
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) {
            return false;
        }

        return in_array($permission, $this->permissions) || in_array('manage', $this->permissions);
    }

    /**
     * IPアドレスが許可されているかチェック
     */
    public function isIpAllowed(string $ip): bool
    {
        if (!$this->allowed_ips) {
            return true; // 制限なし
        }

        return in_array($ip, $this->allowed_ips);
    }

    /**
     * 最終使用日時を更新
     */
    public function updateLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * マスクされたトークンを取得
     */
    public function getMaskedTokenAttribute(): string
    {
        if (!$this->token) {
            return '';
        }

        return substr($this->token, 0, 8) . '...' . substr($this->token, -8);
    }

    /**
     * アクティブなトークンのみ
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
     * 期限切れのトークン
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * 特定の権限を持つトークン
     */
    public function scopeWithPermission($query, $permission)
    {
        return $query->whereJsonContains('permissions', $permission)
            ->orWhereJsonContains('permissions', 'manage');
    }
}