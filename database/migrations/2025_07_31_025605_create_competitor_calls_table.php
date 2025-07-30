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
        Schema::create('competitor_calls', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->comment('端末ID');
            $table->enum('call_type', ['general', 'technical'])->comment('呼び出し種別');
            $table->timestamp('called_at')->comment('呼び出し日時');
            $table->timestamps();
            
            $table->index(['device_id', 'called_at']);
            $table->index('call_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitor_calls');
    }
};
