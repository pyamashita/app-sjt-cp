<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionContent extends Model
{
    protected $fillable = [
        'collection_id',
        'field_id',
        'value',
        'competition_id',
        'player_id',
    ];

    protected $casts = [
        'competition_id' => 'integer',
        'player_id' => 'integer',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(CollectionField::class);
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function getFormattedValueAttribute(): string
    {
        if (is_null($this->value)) {
            return '-';
        }

        return match ($this->field->content_type) {
            'boolean' => $this->value ? 'はい' : 'いいえ',
            'resource' => Resource::find($this->value)?->name ?? $this->value,
            'date' => \Carbon\Carbon::parse($this->value)->format('Y/m/d'),
            'time' => \Carbon\Carbon::parse($this->value)->format('H:i'),
            default => $this->value,
        };
    }
}
