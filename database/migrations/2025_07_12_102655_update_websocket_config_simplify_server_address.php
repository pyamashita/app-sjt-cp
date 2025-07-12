<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // WebSocket設定をlocalhost vs カスタムアドレスのシンプルな設計に変更
        $connection = DB::table('external_connections')
            ->where('service_type', 'websocket_message')
            ->first();

        if ($connection) {
            $config = json_decode($connection->config, true);
            
            // 新しい設計に変更
            $newConfig = $config;
            $newConfig['use_localhost'] = true; // デフォルトはlocalhost使用
            
            // use_device_ip設定を削除
            unset($newConfig['use_device_ip']);
            
            DB::table('external_connections')
                ->where('id', $connection->id)
                ->update([
                    'config' => json_encode($newConfig),
                    'description' => 'WebSocketサーバーへの接続設定（localhost または 指定アドレス）',
                    'updated_at' => now()
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 元の設計に戻す
        $connection = DB::table('external_connections')
            ->where('service_type', 'websocket_message')
            ->first();

        if ($connection) {
            $config = json_decode($connection->config, true);
            
            // 元の設計に戻す
            $oldConfig = $config;
            $oldConfig['use_device_ip'] = true;
            
            // use_localhost設定を削除
            unset($oldConfig['use_localhost']);
            
            DB::table('external_connections')
                ->where('id', $connection->id)
                ->update([
                    'config' => json_encode($oldConfig),
                    'description' => '端末へのメッセージ送信に使用するWebSocketサーバーの設定',
                    'updated_at' => now()
                ]);
        }
    }
};
