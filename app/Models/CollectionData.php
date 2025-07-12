<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionData extends Model
{
    protected $fillable = [
        'collection_id',
        'content_id',
        'competition_id',
        'player_id',
        'value',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(CollectionContent::class, 'content_id');
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function getFormattedValueAttribute()
    {
        if ($this->content) {
            switch ($this->content->content_type) {
                case 'boolean':
                    return $this->value ? 'はい' : 'いいえ';
                case 'resource':
                    if ($this->value) {
                        $resource = Resource::find($this->value);
                        return $resource ? $resource->original_name : 'リソースが見つかりません';
                    }
                    return null;
                case 'date':
                    return $this->value ? \Carbon\Carbon::parse($this->value)->format('Y年m月d日') : null;
                case 'time':
                    return $this->value ? \Carbon\Carbon::parse($this->value)->format('H:i') : null;
                default:
                    return $this->value;
            }
        }
        
        return $this->value;
    }

    public function scopeForContext($query, $collectionId, $competitionId = null, $playerId = null)
    {
        return $query->where('collection_id', $collectionId)
            ->where('competition_id', $competitionId)
            ->where('player_id', $playerId);
    }
}
