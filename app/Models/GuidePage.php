<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuidePage extends Model
{
    protected $fillable = [
        'competition_id',
        'title',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(GuidePageSection::class)->orderBy('sort_order');
    }

    public function activate(): void
    {
        // 同じ大会の他のページを非アクティブにする
        static::where('competition_id', $this->competition_id)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);
        
        // このページをアクティブにする
        $this->update(['is_active' => true]);
    }

    public static function getActiveForCompetition(int $competitionId): ?self
    {
        return static::where('competition_id', $competitionId)
            ->where('is_active', true)
            ->first();
    }
}
