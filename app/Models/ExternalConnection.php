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
    const SERVICE_WEBSOCKET_TIME = 'websocket_time';

    /**
     * 利用可能なサービスタイプ
     */
    public static function getServiceTypes(): array
    {
        return [
            self::SERVICE_WEBSOCKET_MESSAGE => 'WebSocket Echoサーバ',
            self::SERVICE_WEBSOCKET_TIME => '時刻同期WebSocketサーバ',
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
            'use_localhost' => true,
            'server_address' => '',
            'default_port' => 8081,
            'timeout' => 10,
            'retry_count' => 3,
            'retry_delay' => 1000,
            'protocol' => 'ws',
            'path' => '/ws'
        ];
    }

    /**
     * 時刻同期WebSocketの設定を取得
     */
    public static function getTimeWebSocketConfig(): array
    {
        $config = self::getConfig(self::SERVICE_WEBSOCKET_TIME);
        
        return $config ?: [
            'use_localhost' => true,
            'server_address' => '',
            'default_port' => 8081,
            'timeout' => 10,
            'retry_count' => 3,
            'retry_delay' => 1000,
            'protocol' => 'ws',
            'path' => '/ws'
        ];
    }
}
