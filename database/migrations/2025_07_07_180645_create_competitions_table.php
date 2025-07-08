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
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 大会名称
            $table->date('start_date'); // 開催日
            $table->date('end_date'); // 終了日
            $table->string('venue'); // 開催場所
            $table->string('chief_judge')->nullable(); // 競技主査
            $table->json('committee_members')->nullable(); // 競技委員（配列として保存）
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
