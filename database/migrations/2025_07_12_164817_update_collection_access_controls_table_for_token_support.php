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
        Schema::table('collection_access_controls', function (Blueprint $table) {
            // 既存のカラムを削除
            $table->dropColumn(['ip_address', 'description']);
            
            // 新しいカラムを追加
            $table->string('type')->comment('アクセス制御タイプ (ip_whitelist, api_token, token_required)');
            $table->string('value')->comment('制御値 (IPアドレス、API_TOKEN_ID等)');
            $table->boolean('is_active')->default(true)->comment('有効/無効');
            
            // インデックス追加
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collection_access_controls', function (Blueprint $table) {
            // 新しいカラムを削除
            $table->dropIndex(['type', 'is_active']);
            $table->dropColumn(['type', 'value', 'is_active']);
            
            // 元のカラムを復元
            $table->string('ip_address')->comment('許可IPアドレス');
            $table->string('description')->nullable()->comment('説明');
        });
    }
};
