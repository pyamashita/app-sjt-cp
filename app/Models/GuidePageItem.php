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
        'text_content',
        'show_copy_button',
        'collection_id',
        'open_in_new_tab',
        'sort_order',
    ];

    protected $casts = [
        'open_in_new_tab' => 'boolean',
        'show_copy_button' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(GuidePageGroup::class, 'guide_page_group_id');
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function getDisplayUrl($emulatePlayerId = null): string
    {
        if ($this->type === 'resource' && $this->resource) {
            return \App\Helpers\ApiHelper::url('resources/' . $this->resource->id . '/download');
        }
        
        if ($this->type === 'collection' && $this->collection) {
            $params = ['collection' => $this->collection->id];
            
            // エミュレート中の場合、選手IDを追加
            if ($emulatePlayerId) {
                $params['emulate_player_id'] = $emulatePlayerId;
            }
            
            return route('guide.collection.view', $params);
        }
        
        return $this->url ?? '#';
    }

    public function getTarget(): string
    {
        return $this->open_in_new_tab ? '_blank' : '_self';
    }

    public function getTypeDisplayName(): string
    {
        return match($this->type) {
            'resource' => 'リソース',
            'link' => 'リンク',
            'text' => 'テキスト',
            'collection' => 'コレクション',
            default => $this->type,
        };
    }

    public function getTruncatedTextContent(int $length = 100): string
    {
        if ($this->type !== 'text' || !$this->text_content) {
            return '';
        }
        
        return mb_strlen($this->text_content) > $length
            ? mb_substr($this->text_content, 0, $length) . '...'
            : $this->text_content;
    }

    public function isClickable(): bool
    {
        return in_array($this->type, ['resource', 'link', 'collection']);
    }
}
