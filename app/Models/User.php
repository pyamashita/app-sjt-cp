<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * リレーションを常にロードする
     */
    protected $with = ['role'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * ユーザーのロール
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * ユーザーが指定されたロールかどうかを確認
     *
     * @param string $roleName
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    /**
     * ユーザーが管理者かどうかを確認
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * ユーザーが競技委員かどうかを確認
     *
     * @return bool
     */
    public function isCommittee(): bool
    {
        return $this->hasRole('committee');
    }

    /**
     * ユーザーが補佐員かどうかを確認
     *
     * @return bool
     */
    public function isAssistant(): bool
    {
        return $this->hasRole('assistant');
    }

    /**
     * ロール表示名を取得
     *
     * @return string
     */
    public function getRoleDisplayNameAttribute(): string
    {
        return $this->role ? $this->role->display_name : '未設定';
    }

    /**
     * ロール名を取得（旧互換性のため）
     *
     * @return string
     */
    public function getRoleNameAttribute(): string
    {
        return $this->role ? $this->role->name : '';
    }

    /**
     * ユーザーが指定された権限を持っているかチェック
     *
     * @param string $permissionName
     * @return bool
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->role && $this->role->hasPermission($permissionName);
    }

    /**
     * ユーザーがいずれかの権限を持っているかチェック
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->role && $this->role->hasAnyPermission($permissions);
    }

    /**
     * 管理画面にアクセスできるかチェック
     *
     * @return bool
     */
    public function canAccessAdmin(): bool
    {
        return $this->hasPermission('admin_access');
    }

    /**
     * 指定されたURLにアクセスできるかチェック
     *
     * @param string $url
     * @return bool
     */
    public function canAccessUrl(string $url): bool
    {
        return $this->role && $this->role->hasUrlPermission($url);
    }
}
