<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionDay extends Model
{
    protected $fillable = [
        'competition_id',
        'day_name',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * 大会とのリレーション
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * 競技スケジュールとのリレーション
     */
    public function competitionSchedules(): HasMany
    {
        return $this->hasMany(CompetitionSchedule::class)->orderBy('sort_order');
    }

    /**
     * 日付の表示形式を取得
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->date->format('Y年m月d日');
    }

    /**
     * 曜日を取得
     */
    public function getDayOfWeekAttribute(): string
    {
        $dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'];
        return $dayOfWeek[$this->date->dayOfWeek];
    }
}
