<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuidePageGroup extends Model
{
    protected $fillable = [
        'guide_page_section_id',
        'title',
        'sort_order',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(GuidePageSection::class, 'guide_page_section_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GuidePageItem::class)->orderBy('sort_order');
    }
}
