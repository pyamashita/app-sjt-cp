<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competition extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'venue',
        'chief_judge',
        'committee_members',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'committee_members' => 'array',
    ];

    /**
     * 競技日程とのリレーション
     */
    public function competitionDays(): HasMany
    {
        return $this->hasMany(CompetitionDay::class);
    }

    /**
     * 競技委員を配列から文字列に変換
     */
    public function getCommitteeMembersStringAttribute(): string
    {
        return $this->committee_members ? implode(', ', $this->committee_members) : '';
    }

    /**
     * 大会期間（開始日〜終了日）を取得
     */
    public function getPeriodAttribute(): string
    {
        if ($this->start_date->equalTo($this->end_date)) {
            return $this->start_date->format('Y年m月d日');
        }
        
        return $this->start_date->format('Y年m月d日') . ' 〜 ' . $this->end_date->format('Y年m月d日');
    }
}
