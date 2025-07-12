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
        Schema::create('message_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->enum('delivery_status', ['pending', 'sent', 'delivered', 'failed'])->default('pending'); // 配信ステータス
            $table->timestamp('sent_at')->nullable(); // 送信日時
            $table->timestamp('delivered_at')->nullable(); // 配信完了日時
            $table->text('error_message')->nullable(); // エラーメッセージ
            $table->integer('retry_count')->default(0); // 再試行回数
            $table->timestamps();
            
            $table->unique(['message_id', 'device_id']);
            $table->index(['delivery_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_devices');
    }
};
