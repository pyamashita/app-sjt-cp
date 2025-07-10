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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('端末名');
            $table->enum('type', ['PC', 'スマートフォン', 'その他'])->comment('端末種別');
            $table->enum('user_type', ['選手', '競技関係者', 'ネットワーク'])->comment('利用者');
            $table->string('ip_address', 15)->nullable()->comment('IPアドレス（IPv4）');
            $table->string('mac_address', 17)->nullable()->comment('MACアドレス');
            $table->timestamps();
            
            $table->index('name');
            $table->index('type');
            $table->index('user_type');
            
            $table->comment('端末管理テーブル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};