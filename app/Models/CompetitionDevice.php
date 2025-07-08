<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'device_id',
        'player_number',
    ];

    /**
     * この割り当てが属する大会を取得
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * この割り当てが属する端末を取得
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * 大会でフィルタリング
     */
    public function scopeForCompetition($query, $competitionId)
    {
        return $query->where('competition_id', $competitionId);
    }

    /**
     * 検索スコープ
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('player_number', 'like', "%{$search}%")
              ->orWhereHas('device', function ($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('mac_address', 'like', "%{$search}%");
              });
        });
    }

    /**
     * CSVエクスポート用のデータ配列を取得
     */
    public function toCsvArray(): array
    {
        return [
            $this->player_number,
            $this->device->name,
            $this->device->type,
            $this->device->ip_address ?? '',
            $this->device->mac_address ?? '',
        ];
    }

    /**
     * CSVヘッダーを取得
     */
    public static function getCsvHeaders(): array
    {
        return ['選手番号', '端末名', '端末種別', 'IPアドレス', 'MACアドレス'];
    }

    /**
     * CSVインポート用のヘッダーを取得
     */
    public static function getImportCsvHeaders(): array
    {
        return ['選手番号', '端末名'];
    }
}