<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuidePageSection extends Model
{
    protected $fillable = [
        'guide_page_id',
        'title',
        'sort_order',
    ];

    public function guidePage(): BelongsTo
    {
        return $this->belongsTo(GuidePage::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(GuidePageGroup::class)->orderBy('sort_order');
    }
}
