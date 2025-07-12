<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'device_id',
        'delivery_status',
        'sent_at',
        'delivered_at',
        'error_message',
        'retry_count',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    /**
     * 配信ステータスの選択肢
     */
    public static function getDeliveryStatuses(): array
    {
        return [
            'pending' => '送信待ち',
            'sent' => '送信済み',
            'delivered' => '配信完了',
            'failed' => '送信失敗',
        ];
    }

    /**
     * 配信ステータスの表示名
     */
    public function getDeliveryStatusDisplayAttribute(): string
    {
        return self::getDeliveryStatuses()[$this->delivery_status] ?? $this->delivery_status;
    }

    /**
     * メッセージ
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * 端末
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * 送信失敗かどうか
     */
    public function isFailed(): bool
    {
        return $this->delivery_status === 'failed';
    }

    /**
     * 送信済みかどうか
     */
    public function isSent(): bool
    {
        return in_array($this->delivery_status, ['sent', 'delivered']);
    }

    /**
     * 再送信可能かどうか
     */
    public function canRetry(): bool
    {
        return $this->delivery_status === 'failed' && $this->retry_count < 3;
    }
}