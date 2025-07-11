<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommitteeMember extends Model
{
    protected $fillable = [
        'name',
        'name_kana',
        'organization',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * アクティブな競技委員のみを取得するスコープ
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 名前（ふりがな）で並び替えるスコープ
     */
    public function scopeOrderByNameKana($query)
    {
        return $query->orderBy('name_kana');
    }

    /**
     * 名前で検索するスコープ
     */
    public function scopeSearchByName($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('name_kana', 'like', "%{$search}%")
              ->orWhere('organization', 'like', "%{$search}%");
        });
    }

    /**
     * 表示用の名前（所属付き）を取得
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->organization) {
            return "{$this->name} ({$this->organization})";
        }
        return $this->name;
    }

    /**
     * 表示用の名前（ふりがな付き）を取得
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->name} ({$this->name_kana})";
    }
}
