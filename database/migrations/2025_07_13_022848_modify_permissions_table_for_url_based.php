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
        Schema::table('permissions', function (Blueprint $table) {
            // 新しいURL関連のカラムを追加
            $table->string('url')->after('name')->comment('アクセス制御対象のURL');
            $table->text('remarks')->nullable()->after('description')->comment('機能備考');
            
            // 既存カラムを調整
            $table->string('display_name')->comment('機能タイトル')->change();
            $table->text('description')->nullable()->comment('機能説明')->change();
            
            // 不要になったカラムを削除
            $table->dropColumn(['category', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            // カラムを元に戻す
            $table->dropColumn(['url', 'remarks']);
            $table->string('category')->nullable()->comment('権限カテゴリ');
            $table->integer('sort_order')->default(0)->comment('表示順序');
        });
    }
};