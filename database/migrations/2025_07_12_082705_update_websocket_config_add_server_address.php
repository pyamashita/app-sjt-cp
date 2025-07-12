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
        // WebSocket設定にサーバーアドレス設定を追加
        $connection = DB::table('external_connections')
            ->where('service_type', 'websocket_message')
            ->first();

        if ($connection) {
            $config = json_decode($connection->config, true);
            
            // サーバーアドレス設定を追加
            $config['server_address'] = ''; // デフォルトは空（端末のIPアドレスを使用）
            $config['use_device_ip'] = true; // 端末IPアドレスを使用するかどうか
            
            DB::table('external_connections')
                ->where('id', $connection->id)
                ->update([
                    'config' => json_encode($config),
                    'updated_at' => now()
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // WebSocket設定からサーバーアドレス設定を削除
        $connection = DB::table('external_connections')
            ->where('service_type', 'websocket_message')
            ->first();

        if ($connection) {
            $config = json_decode($connection->config, true);
            
            // サーバーアドレス設定を削除
            unset($config['server_address']);
            unset($config['use_device_ip']);
            
            DB::table('external_connections')
                ->where('id', $connection->id)
                ->update([
                    'config' => json_encode($config),
                    'updated_at' => now()
                ]);
        }
    }
};
