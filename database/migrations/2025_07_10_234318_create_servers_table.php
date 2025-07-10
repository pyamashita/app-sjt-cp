<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['player', 'database', 'dns', 'other'])->comment('サーバ種類: player=選手サーバー, database=DBサーバー, dns=DNSサーバー, other=その他競技サーバー');
            $table->string('ip_address')->comment('IPアドレス');
            $table->string('hostname')->comment('ホスト名');
            $table->string('username')->comment('ユーザ名');
            $table->string('password')->comment('パスワード');
            $table->string('web_document_root')->comment('Webドキュメントルート');
            $table->text('description')->nullable()->comment('説明');
            $table->boolean('is_active')->default(true)->comment('アクティブ状態');
            $table->json('status_info')->nullable()->comment('サーバ状態情報（将来の監視API用）');
            $table->timestamps();
            
            // インデックス
            $table->index(['type', 'is_active']);
            $table->unique(['ip_address', 'hostname']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
