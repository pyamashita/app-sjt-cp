<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'send_method',
        'title',
        'content',
        'link',
        'resource_id',
        'status',
        'scheduled_at',
        'sent_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * 送信方法の選択肢
     */
    public static function getSendMethods(): array
    {
        return [
            'immediate' => '即時送信',
            'scheduled' => '予約送信',
        ];
    }

    /**
     * ステータスの選択肢
     */
    public static function getStatuses(): array
    {
        return [
            'draft' => '下書き',
            'pending' => '送信待ち',
            'sending' => '送信中',
            'completed' => '送信完了',
            'failed' => '送信失敗',
        ];
    }

    /**
     * 送信方法の表示名
     */
    public function getSendMethodDisplayAttribute(): string
    {
        return self::getSendMethods()[$this->send_method] ?? $this->send_method;
    }

    /**
     * ステータスの表示名
     */
    public function getStatusDisplayAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * 本文の省略表示（50文字）
     */
    public function getContentPreviewAttribute(): string
    {
        return mb_strlen($this->content) > 50 
            ? mb_substr($this->content, 0, 50) . '...' 
            : $this->content;
    }

    /**
     * 作成者
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 添付リソース（画像）
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    /**
     * 送信対象の端末（中間テーブル経由）
     */
    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(Device::class, 'message_devices')
            ->withPivot('delivery_status', 'sent_at', 'delivered_at', 'error_message', 'retry_count')
            ->withTimestamps();
    }

    /**
     * メッセージデバイスの詳細情報
     */
    public function messageDevices(): HasMany
    {
        return $this->hasMany(MessageDevice::class);
    }

    /**
     * 送信可能かチェック
     */
    public function canSend(): bool
    {
        return in_array($this->status, ['draft', 'pending', 'failed']);
    }

    /**
     * 編集可能かチェック
     */
    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'pending']);
    }

    /**
     * 削除可能かチェック
     */
    public function canDelete(): bool
    {
        return $this->status !== 'sending';
    }

    /**
     * スコープ: ステータスでフィルタ
     */
    public function scopeStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * スコープ: 送信方法でフィルタ
     */
    public function scopeSendMethod($query, $method)
    {
        if ($method) {
            return $query->where('send_method', $method);
        }
        return $query;
    }

    /**
     * スコープ: 検索
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }
        return $query;
    }
}