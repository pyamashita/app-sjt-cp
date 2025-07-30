<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CompetitorCall extends Model
{
    use HasFactory;

    protected $table = 'competitor_calls';

    protected $fillable = [
        'device_id',
        'call_type',
        'called_at',
    ];

    protected $casts = [
        'called_at' => 'datetime',
    ];

    /**
     * 呼び出し種別の定数
     */
    const CALL_TYPE_GENERAL = 'general';
    const CALL_TYPE_TECHNICAL = 'technical';

    /**
     * 呼び出し種別の表示名を取得
     */
    public static function getCallTypeNames(): array
    {
        return [
            self::CALL_TYPE_GENERAL => '一般呼び出し',
            self::CALL_TYPE_TECHNICAL => '技術的呼び出し',
        ];
    }

    /**
     * 呼び出し種別の表示名を取得
     */
    public function getCallTypeNameAttribute(): string
    {
        $names = self::getCallTypeNames();
        return $names[$this->call_type] ?? $this->call_type;
    }

    /**
     * 日付範囲でのスコープ
     */
    public function scopeDateRange($query, $startDate = null, $endDate = null)
    {
        if ($startDate) {
            $query->where('called_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('called_at', '<=', $endDate);
        }
        return $query;
    }

    /**
     * 端末IDでのスコープ
     */
    public function scopeByDevice($query, $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    /**
     * 呼び出し種別でのスコープ
     */
    public function scopeByCallType($query, $callType)
    {
        return $query->where('call_type', $callType);
    }
}
