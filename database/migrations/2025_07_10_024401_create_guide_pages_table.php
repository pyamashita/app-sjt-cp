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
        Schema::create('guide_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_active')->default(false);
            $table->json('settings')->nullable(); // カスタム設定用
            $table->timestamps();
            
            // 大会ごとに1つのアクティブページのみ許可
            $table->unique(['competition_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guide_pages');
    }
};
