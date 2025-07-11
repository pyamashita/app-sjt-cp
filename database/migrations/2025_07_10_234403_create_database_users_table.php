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
        Schema::create('database_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained('servers')->onDelete('cascade')->comment('所属するDBサーバーID');
            $table->string('username')->comment('DBユーザ名');
            $table->string('password')->comment('パスワード');
            $table->json('privileges')->nullable()->comment('権限設定（JSON形式）');
            $table->string('allowed_hosts', 255)->default('%')->comment('接続許可ホスト');
            $table->text('description')->nullable()->comment('説明');
            $table->boolean('is_active')->default(true)->comment('アクティブ状態');
            $table->timestamps();
            
            // インデックス
            $table->index(['server_id', 'is_active']);
            $table->unique(['server_id', 'username']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('database_users');
    }
};
