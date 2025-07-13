<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * このロールを持つユーザー
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * ロール名による検索
     */
    public static function findByName(string $name): ?Role
    {
        return static::where('name', $name)->first();
    }

    /**
     * このロールの権限
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * 権限を持っているかチェック（名前ベース）
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()
            ->where('permissions.name', $permissionName)
            ->where('permissions.is_active', true)
            ->exists();
    }

    /**
     * URLに対する権限を持っているかチェック
     */
    public function hasUrlPermission(string $url): bool
    {
        $permission = Permission::findByUrlPattern($url);
        
        if (!$permission) {
            // 権限設定がないURLは全てアクセス可能
            return true;
        }
        
        return $this->permissions()
            ->where('permissions.id', $permission->id)
            ->where('permissions.is_active', true)
            ->exists();
    }

    /**
     * 複数の権限を持っているかチェック
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->permissions()
            ->whereIn('permissions.name', $permissions)
            ->where('permissions.is_active', true)
            ->exists();
    }

    /**
     * 権限を同期（既存を削除して新しく設定）
     */
    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }

    /**
     * アクティブなロールのみを取得
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
