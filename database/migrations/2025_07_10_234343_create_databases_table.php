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
        Schema::create('databases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained('servers')->onDelete('cascade')->comment('所属するDBサーバーID');
            $table->string('name')->comment('データベース名');
            $table->string('charset', 50)->default('utf8mb4')->comment('文字セット');
            $table->string('collation', 100)->default('utf8mb4_unicode_ci')->comment('照合順序');
            $table->text('description')->nullable()->comment('説明');
            $table->boolean('is_active')->default(true)->comment('アクティブ状態');
            $table->timestamps();
            
            // インデックス
            $table->index(['server_id', 'is_active']);
            $table->unique(['server_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('databases');
    }
};
