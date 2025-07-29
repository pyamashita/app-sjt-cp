<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'name',
        'type',
        'user_type',
        'ip_address',
        'mac_address',
    ];

    /**
     * モデルのイベント処理
     */
    protected static function boot()
    {
        parent::boot();

        // 新規作成時に端末IDを自動生成
        static::creating(function ($device) {
            if (empty($device->device_id)) {
                $device->device_id = self::generateUniqueDeviceId();
            }
        });
    }

    /**
     * ユニークな端末IDを生成
     */
    public static function generateUniqueDeviceId(): string
    {
        do {
            // TML-xxxx形式でID生成（英数字小文字）
            $randomPart = substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz0123456789', 4)), 0, 4);
            $deviceId = 'TML-' . $randomPart;
        } while (self::where('device_id', $deviceId)->exists());

        return $deviceId;
    }

    /**
     * 端末種別の選択肢を取得
     */
    public static function getTypes(): array
    {
        return [
            'PC' => 'PC',
            'スマートフォン' => 'スマートフォン',
            'その他' => 'その他',
        ];
    }

    /**
     * 利用者種別の選択肢を取得
     */
    public static function getUserTypes(): array
    {
        return [
            '選手' => '選手',
            '競技関係者' => '競技関係者',
            'ネットワーク' => 'ネットワーク',
        ];
    }

    /**
     * この端末が割り当てられている大会を取得
     */
    public function competitions(): BelongsToMany
    {
        return $this->belongsToMany(Competition::class, 'competition_devices')
            ->withPivot('player_number')
            ->withTimestamps();
    }

    /**
     * この端末の競技割り当てを取得
     */
    public function competitionDevices(): HasMany
    {
        return $this->hasMany(CompetitionDevice::class);
    }

    /**
     * 検索スコープ
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('device_id', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%")
              ->orWhere('ip_address', 'like', "%{$search}%")
              ->orWhere('mac_address', 'like', "%{$search}%");
        });
    }

    /**
     * 端末種別でフィルタリング
     */
    public function scopeOfType($query, $type)
    {
        if ($type) {
            return $query->where('type', $type);
        }
        return $query;
    }

    /**
     * 利用者種別でフィルタリング
     */
    public function scopeOfUserType($query, $userType)
    {
        if ($userType) {
            return $query->where('user_type', $userType);
        }
        return $query;
    }

    /**
     * 選手用端末のみを取得
     */
    public function scopeForPlayers($query)
    {
        return $query->where('user_type', '選手');
    }

    /**
     * CSVエクスポート用のデータ配列を取得
     */
    public function toCsvArray(): array
    {
        return [
            $this->device_id,
            $this->name,
            $this->type,
            $this->user_type,
            $this->ip_address ?? '',
            $this->mac_address ?? '',
            $this->created_at->format('Y-m-d'),
        ];
    }

    /**
     * CSVヘッダーを取得
     */
    public static function getCsvHeaders(): array
    {
        return ['端末ID', '端末名', '端末種別', '利用者', 'IPアドレス', 'MACアドレス', '登録日'];
    }
}