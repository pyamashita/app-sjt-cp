<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DnsRecord extends Model
{
    protected $fillable = [
        'server_id',
        'name',
        'type',
        'value',
        'ttl',
        'priority',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'ttl' => 'integer',
        'priority' => 'integer',
    ];

    /**
     * DNSレコードタイプの定義
     */
    public const TYPES = [
        'A' => 'A（IPv4アドレス）',
        'AAAA' => 'AAAA（IPv6アドレス）',
        'CNAME' => 'CNAME（正規名）',
        'MX' => 'MX（メール交換）',
        'TXT' => 'TXT（テキスト）',
        'PTR' => 'PTR（ポインタ）',
        'SRV' => 'SRV（サービス）',
        'NS' => 'NS（ネームサーバー）',
        'SOA' => 'SOA（権威開始）',
    ];

    /**
     * 所属するサーバー
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * レコードタイプの表示名を取得
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * アクティブなDNSレコードのみを取得するスコープ
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 指定された種類のDNSレコードを取得するスコープ
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
