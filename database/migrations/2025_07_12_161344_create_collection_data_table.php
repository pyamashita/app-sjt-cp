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
        Schema::create('collection_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections')->onDelete('cascade')->comment('コレクションID');
            $table->foreignId('content_id')->constrained('collection_contents')->onDelete('cascade')->comment('コンテンツID');
            $table->foreignId('competition_id')->nullable()->constrained('competitions')->onDelete('cascade')->comment('大会ID（大会ごと管理の場合）');
            $table->foreignId('player_id')->nullable()->constrained('players')->onDelete('cascade')->comment('選手ID（選手ごと管理の場合）');
            $table->longText('value')->nullable()->comment('データ値');
            $table->timestamps();
            
            $table->index(['collection_id']);
            $table->index(['content_id']);
            $table->index(['competition_id']);
            $table->index(['player_id']);
            $table->unique(['collection_id', 'content_id', 'competition_id', 'player_id'], 'collection_data_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_data');
    }
};
