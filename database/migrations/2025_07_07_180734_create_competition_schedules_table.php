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
        Schema::create('competition_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_day_id')->constrained()->onDelete('cascade');
            $table->time('start_time'); // 開始時刻
            $table->string('content'); // 内容
            $table->text('notes')->nullable(); // 備考
            $table->boolean('count_up')->default(false); // カウントアップ表示エフェクト
            $table->boolean('auto_advance')->default(false); // 自動送り表示エフェクト
            $table->integer('sort_order')->default(0); // 表示順
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_schedules');
    }
};
