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
        Schema::create('competition_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->string('player_number')->comment('選手番号');
            $table->timestamps();
            
            // 一つの大会で同じ端末は一度しか割り当てられない
            $table->unique(['competition_id', 'device_id']);
            // 一つの大会で同じ選手番号は一度しか使えない
            $table->unique(['competition_id', 'player_number']);
            
            $table->index('player_number');
            
            $table->comment('競技端末割り当てテーブル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_devices');
    }
};