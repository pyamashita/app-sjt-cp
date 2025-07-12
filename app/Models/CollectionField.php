<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionField extends Model
{
    protected $fillable = [
        'collection_id',
        'name',
        'content_type',
        'max_length',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'max_length' => 'integer',
        'sort_order' => 'integer',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(CollectionContent::class, 'field_id');
    }

    public function getContentTypeDisplayNameAttribute(): string
    {
        return match ($this->content_type) {
            'string' => '文字列',
            'text' => 'テキスト',
            'boolean' => '真偽値',
            'resource' => 'リソース',
            'date' => '日付',
            'time' => '時刻',
            default => $this->content_type,
        };
    }

    public function getValidationRulesAttribute(): array
    {
        $rules = [];
        
        if ($this->is_required) {
            $rules[] = 'required';
        }
        
        switch ($this->content_type) {
            case 'string':
                $rules[] = 'string';
                if ($this->max_length) {
                    $rules[] = "max:{$this->max_length}";
                } else {
                    $rules[] = 'max:255';
                }
                break;
            case 'text':
                $rules[] = 'string';
                if ($this->max_length) {
                    $rules[] = "max:{$this->max_length}";
                } else {
                    $rules[] = 'max:5000';
                }
                break;
            case 'boolean':
                $rules[] = 'boolean';
                break;
            case 'resource':
                $rules[] = 'exists:resources,id';
                break;
            case 'date':
                $rules[] = 'date';
                break;
            case 'time':
                $rules[] = 'date_format:H:i';
                break;
        }
        
        return $rules;
    }

    public function getCompletionRateAttribute(): array
    {
        $collection = $this->collection;
        
        if (!$collection->is_player_managed) {
            return ['completed' => 0, 'total' => 0];
        }
        
        // すべての選手を取得
        $totalPlayers = \App\Models\Player::count();
        
        // このフィールドに対してコンテンツが入力されている選手数を取得
        $completedPlayers = $this->contents()
            ->whereNotNull('player_id')
            ->distinct('player_id')
            ->count('player_id');
        
        return [
            'completed' => $completedPlayers,
            'total' => $totalPlayers
        ];
    }
}
