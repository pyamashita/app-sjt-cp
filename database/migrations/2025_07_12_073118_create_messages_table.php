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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->enum('send_method', ['immediate', 'scheduled']); // 送信方法: 即時、予約
            $table->string('title', 200)->nullable(); // タイトル（全角50文字 = 200バイト）
            $table->text('content'); // 本文（全角1000文字）
            $table->string('link', 2000)->nullable(); // リンク
            $table->foreignId('resource_id')->nullable()->constrained()->onDelete('set null'); // 画像リソース
            $table->enum('status', ['draft', 'pending', 'sending', 'completed', 'failed'])->default('draft'); // 送信ステータス
            $table->timestamp('scheduled_at')->nullable(); // 予約送信日時
            $table->timestamp('sent_at')->nullable(); // 送信開始日時
            $table->timestamp('completed_at')->nullable(); // 送信完了日時
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // 作成者
            $table->timestamps();
            
            $table->index(['status', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
