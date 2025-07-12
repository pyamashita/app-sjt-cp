<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionContent extends Model
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

    public function data(): HasMany
    {
        return $this->hasMany(CollectionData::class, 'content_id');
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
}
