<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'year',
        'is_competition_managed',
        'is_player_managed',
    ];

    protected $casts = [
        'is_competition_managed' => 'boolean',
        'is_player_managed' => 'boolean',
        'year' => 'integer',
    ];

    public function contents(): HasMany
    {
        return $this->hasMany(CollectionContent::class)->orderBy('sort_order');
    }

    public function accessControls(): HasMany
    {
        return $this->hasMany(CollectionAccessControl::class);
    }

    public function data(): HasMany
    {
        return $this->hasMany(CollectionData::class);
    }

    public function getDisplayNameAttribute($value): string
    {
        return $value ?: $this->name;
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }

    public function scopeCompetitionManaged($query)
    {
        return $query->where('is_competition_managed', true);
    }

    public function scopePlayerManaged($query)
    {
        return $query->where('is_player_managed', true);
    }
}
