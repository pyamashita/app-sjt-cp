<?php

namespace Database\Seeders;

use App\Models\ExternalConnection;
use Illuminate\Database\Seeder;

class ExternalConnectionSeeder extends Seeder
{
    /**
     * 外部接続設定のシード
     */
    public function run(): void
    {
        // WebSocketメッセージ送信設定
        ExternalConnection::updateOrCreate(
            [
                'service_type' => ExternalConnection::SERVICE_WEBSOCKET_MESSAGE,
            ],
            [
                'name' => 'WebSocket Echoサーバ',
                'description' => 'WebSocket Echoサーバ接続設定',
                'is_active' => true,
                'config' => [
                    'use_localhost' => false,
                    'server_address' => 'host.docker.internal',
                    'default_port' => 8081,
                    'timeout' => 10,
                    'retry_count' => 3,
                    'retry_delay' => 1000,
                    'protocol' => 'ws',
                    'path' => '/message'
                ],
                'updated_by' => null, // システム設定のため null
            ]
        );

        // 将来的に他の外部接続設定を追加する場合はここに記述
        // 例：API接続設定、データベース接続設定など
    }
}