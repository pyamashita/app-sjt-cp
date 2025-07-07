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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('選手名');
            $table->string('prefecture')->comment('都道府県');
            $table->string('affiliation')->nullable()->comment('所属');
            $table->enum('gender', ['male', 'female', 'other'])->comment('性別（male:男性, female:女性, other:その他）');
            $table->timestamps();
            
            // インデックス
            $table->index(['prefecture']);
            $table->index(['name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};