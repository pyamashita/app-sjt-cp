<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionAccessControl extends Model
{
    protected $fillable = [
        'collection_id',
        'type',
        'value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function apiToken(): BelongsTo
    {
        return $this->belongsTo(ApiToken::class, 'value');
    }

    public function getTypeDisplayNameAttribute(): string
    {
        return match ($this->type) {
            'ip_whitelist' => 'IP許可',
            'api_token' => 'APIトークン',
            'token_required' => 'トークン必須',
            default => $this->type,
        };
    }

    public function getDisplayValueAttribute(): string
    {
        return match ($this->type) {
            'api_token' => $this->apiToken?->name ?? "ID: {$this->value}",
            default => $this->value,
        };
    }

    public static function isAccessAllowed(Collection $collection, string $ipAddress, ?string $apiToken = null): bool
    {
        $activeControls = $collection->accessControls()->where('is_active', true)->get();
        
        if ($activeControls->isEmpty()) {
            return true;
        }

        foreach ($activeControls as $control) {
            switch ($control->type) {
                case 'ip_whitelist':
                    if ($control->value === $ipAddress) {
                        return true;
                    }
                    break;
                    
                case 'api_token':
                    if ($apiToken && $control->apiToken && $control->apiToken->token === $apiToken) {
                        return true;
                    }
                    break;
                    
                case 'token_required':
                    if ($apiToken) {
                        return true;
                    }
                    break;
            }
        }

        return false;
    }
}
