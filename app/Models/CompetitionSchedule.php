<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionSchedule extends Model
{
    protected $fillable = [
        'competition_day_id',
        'start_time',
        'content',
        'notes',
        'count_up',
        'auto_advance',
        'sort_order',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'count_up' => 'boolean',
        'auto_advance' => 'boolean',
    ];

    /**
     * 競技日程とのリレーション
     */
    public function competitionDay(): BelongsTo
    {
        return $this->belongsTo(CompetitionDay::class);
    }

    /**
     * 開始時刻の表示形式を取得
     */
    public function getFormattedStartTimeAttribute(): string
    {
        return $this->start_time->format('H:i');
    }

    /**
     * 表示エフェクトの文字列を取得
     */
    public function getEffectsStringAttribute(): string
    {
        $effects = [];
        if ($this->count_up) {
            $effects[] = 'カウントアップ';
        }
        if ($this->auto_advance) {
            $effects[] = '自動送り';
        }
        
        return empty($effects) ? 'なし' : implode(', ', $effects);
    }
}
