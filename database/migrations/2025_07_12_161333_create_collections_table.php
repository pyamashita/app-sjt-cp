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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('コレクション名（半角英数字+記号）');
            $table->string('display_name')->nullable()->comment('表示名');
            $table->text('description')->nullable()->comment('備考');
            $table->year('year')->nullable()->comment('大会年度');
            $table->boolean('is_competition_managed')->default(false)->comment('大会ごとに管理');
            $table->boolean('is_player_managed')->default(false)->comment('選手ごとに管理');
            $table->timestamps();
            
            $table->index(['year']);
            $table->index(['is_competition_managed']);
            $table->index(['is_player_managed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
