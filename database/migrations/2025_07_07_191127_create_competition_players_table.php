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
        Schema::create('competition_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->onDelete('cascade')->comment('大会ID');
            $table->foreignId('player_id')->constrained()->onDelete('cascade')->comment('選手ID');
            $table->string('player_number')->comment('選手番号');
            $table->timestamps();
            
            // インデックス・制約
            $table->unique(['competition_id', 'player_id'], 'competition_player_unique');
            $table->unique(['competition_id', 'player_number'], 'competition_player_number_unique');
            $table->index(['competition_id']);
            $table->index(['player_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_players');
    }
};