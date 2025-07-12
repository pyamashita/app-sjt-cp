<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionAccessControl extends Model
{
    protected $fillable = [
        'collection_id',
        'ip_address',
        'description',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public static function isIpAllowed(Collection $collection, string $ipAddress): bool
    {
        if ($collection->accessControls()->count() === 0) {
            return true;
        }

        return $collection->accessControls()
            ->where('ip_address', $ipAddress)
            ->exists();
    }
}
