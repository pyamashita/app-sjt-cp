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
        Schema::create('external_connections', function (Blueprint $table) {
            $table->id();
            $table->string('service_type', 50)->unique()->comment('サービス種別');
            $table->string('name', 100)->comment('接続名');
            $table->json('config')->comment('接続設定（JSON形式）');
            $table->boolean('is_active')->default(true)->comment('有効フラグ');
            $table->text('description')->nullable()->comment('説明');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('最終更新者');
            $table->timestamps();
            
            $table->index('service_type');
            $table->index('is_active');
            
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
        
        // 初期データ挿入
        DB::table('external_connections')->insert([
            'service_type' => 'websocket_message',
            'name' => 'メッセージ送受信用WebSocketサーバー',
            'config' => json_encode([
                'use_localhost' => true,
                'server_address' => '',
                'default_port' => 8080,
                'timeout' => 10,
                'retry_count' => 3,
                'retry_delay' => 1000,
                'protocol' => 'ws',
                'path' => '/message'
            ]),
            'is_active' => true,
            'description' => 'WebSocketサーバーへの接続設定（localhost または 指定アドレス）',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_connections');
    }
};
