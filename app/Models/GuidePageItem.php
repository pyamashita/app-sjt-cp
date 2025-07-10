<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuidePageItem extends Model
{
    protected $fillable = [
        'guide_page_group_id',
        'type',
        'title',
        'url',
        'resource_id',
        'open_in_new_tab',
        'sort_order',
    ];

    protected $casts = [
        'open_in_new_tab' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(GuidePageGroup::class, 'guide_page_group_id');
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function getDisplayUrl(): string
    {
        if ($this->type === 'resource' && $this->resource) {
            return \App\Helpers\ApiHelper::url('resources/' . $this->resource->id . '/download');
        }
        
        return $this->url ?? '#';
    }

    public function getTarget(): string
    {
        return $this->open_in_new_tab ? '_blank' : '_self';
    }
}
