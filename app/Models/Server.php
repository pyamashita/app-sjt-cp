<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Server extends Model
{
    protected $fillable = [
        'type',
        'ip_address',
        'hostname',
        'username',
        'password',
        'web_document_root',
        'description',
        'is_active',
        'status_info',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'status_info' => 'array',
        'password' => 'encrypted',
    ];

    /**
     * サーバ種類の定義
     */
    public const TYPES = [
        'player' => '選手サーバー',
        'database' => 'DBサーバー',
        'dns' => 'DNSサーバー',
        'other' => 'その他競技サーバー',
    ];

    /**
     * サーバ種類の表示名を取得
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * データベース一覧を取得
     */
    public function databases(): HasMany
    {
        return $this->hasMany(Database::class);
    }

    /**
     * データベースユーザー一覧を取得
     */
    public function databaseUsers(): HasMany
    {
        return $this->hasMany(DatabaseUser::class);
    }

    /**
     * DNSレコード一覧を取得
     */
    public function dnsRecords(): HasMany
    {
        return $this->hasMany(DnsRecord::class);
    }

    /**
     * DBサーバーかどうかを判定
     */
    public function isDatabaseServer(): bool
    {
        return $this->type === 'database';
    }

    /**
     * DNSサーバーかどうかを判定
     */
    public function isDnsServer(): bool
    {
        return $this->type === 'dns';
    }

    /**
     * 選手サーバーかどうかを判定
     */
    public function isPlayerServer(): bool
    {
        return $this->type === 'player';
    }

    /**
     * アクティブなサーバーのみを取得するスコープ
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 指定された種類のサーバーを取得するスコープ
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
