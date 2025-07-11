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
        Schema::create('committee_members', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('名前');
            $table->string('name_kana')->comment('名前ふりがな');
            $table->string('organization')->nullable()->comment('所属');
            $table->text('description')->nullable()->comment('備考・説明');
            $table->boolean('is_active')->default(true)->comment('アクティブ状態');
            $table->timestamps();
            
            // インデックス
            $table->index(['is_active', 'name']);
            $table->index('name_kana');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committee_members');
    }
};
