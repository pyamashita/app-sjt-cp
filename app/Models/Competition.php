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
     * 大会選手割り当てとのリレーション
     */
    public function competitionPlayers(): HasMany
    {
        return $this->hasMany(CompetitionPlayer::class);
    }

    /**
     * 参加選手とのリレーション（多対多）
     */
    public function players()
    {
        return $this->belongsToMany(Player::class, 'competition_players')
            ->withPivot('player_number')
            ->withTimestamps();
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

    /**
     * 大会で使用する端末
     */
    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(Device::class, 'competition_devices')
            ->withPivot('player_number')
            ->withTimestamps();
    }

    /**
     * 大会の端末割り当て
     */
    public function competitionDevices(): HasMany
    {
        return $this->hasMany(CompetitionDevice::class);
    }
}
