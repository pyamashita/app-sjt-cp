<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Competition extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'venue',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
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
     * 競技委員を文字列で取得
     */
    public function getCommitteeMembersStringAttribute(): string
    {
        $members = [];
        
        // 競技主査を最初に追加
        $chiefJudge = $this->chiefJudge();
        if ($chiefJudge) {
            $members[] = $chiefJudge->display_name . '（競技主査）';
        }
        
        // 競技委員を追加
        $judges = $this->judges()->get();
        foreach ($judges as $judge) {
            $members[] = $judge->display_name;
        }
        
        return implode(', ', $members);
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

    /**
     * 競技委員とのリレーション（多対多）
     */
    public function committeeMembers(): BelongsToMany
    {
        return $this->belongsToMany(CommitteeMember::class, 'competition_committee_member')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * 競技主査を取得
     */
    public function chiefJudge()
    {
        try {
            return $this->committeeMembers()->wherePivot('role', '競技主査')->first();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 競技委員（主査以外）を取得
     */
    public function judges()
    {
        try {
            return $this->committeeMembers()->wherePivot('role', '競技委員');
        } catch (\Exception $e) {
            return collect();
        }
    }
}
