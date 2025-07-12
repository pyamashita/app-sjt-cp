<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalConnection extends Model
{
    protected $fillable = [
        'service_type',
        'name',
        'config',
        'is_active',
        'description',
        'updated_by',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * サービスタイプの定義
     */
    const SERVICE_WEBSOCKET_MESSAGE = 'websocket_message';

    /**
     * 利用可能なサービスタイプ
     */
    public static function getServiceTypes(): array
    {
        return [
            self::SERVICE_WEBSOCKET_MESSAGE => 'メッセージ送受信用WebSocketサーバー',
        ];
    }

    /**
     * 最終更新者
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 特定のサービスタイプの設定を取得
     */
    public static function getConfig(string $serviceType): ?array
    {
        $connection = self::where('service_type', $serviceType)
            ->where('is_active', true)
            ->first();

        return $connection ? $connection->config : null;
    }

    /**
     * WebSocketメッセージ送信の設定を取得
     */
    public static function getWebSocketConfig(): array
    {
        $config = self::getConfig(self::SERVICE_WEBSOCKET_MESSAGE);
        
        return $config ?: [
            'server_address' => '',
            'use_device_ip' => true,
            'default_port' => 8080,
            'timeout' => 10,
            'retry_count' => 3,
            'retry_delay' => 1000,
            'protocol' => 'ws',
            'path' => '/message'
        ];
    }
}
