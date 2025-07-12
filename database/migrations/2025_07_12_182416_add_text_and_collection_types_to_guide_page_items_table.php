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
        Schema::table('guide_page_items', function (Blueprint $table) {
            // 新しいtypeを追加するため、既存のenum列を変更
            DB::statement("ALTER TABLE guide_page_items MODIFY COLUMN type ENUM('resource', 'link', 'text', 'collection')");
            
            // テキストタイプ用のフィールド
            $table->text('text_content')->nullable()->comment('テキストタイプの内容（255文字まで）');
            $table->boolean('show_copy_button')->default(false)->comment('テキストにコピーボタンを表示するか');
            
            // コレクションタイプ用のフィールド
            $table->foreignId('collection_id')->nullable()->constrained()->nullOnDelete()->comment('コレクションタイプで参照するコレクション');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guide_page_items', function (Blueprint $table) {
            // 追加したカラムを削除
            $table->dropColumn(['text_content', 'show_copy_button', 'collection_id']);
            
            // enumを元に戻す
            DB::statement("ALTER TABLE guide_page_items MODIFY COLUMN type ENUM('resource', 'link')");
        });
    }
};
