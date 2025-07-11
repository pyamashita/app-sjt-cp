<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatabaseUser extends Model
{
    protected $fillable = [
        'server_id',
        'username',
        'password',
        'privileges',
        'allowed_hosts',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'privileges' => 'array',
        'password' => 'encrypted',
    ];

    /**
     * 所属するサーバー
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * アクティブなDBユーザーのみを取得するスコープ
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
