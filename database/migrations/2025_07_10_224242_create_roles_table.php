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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // システム内部で使用するキー（admin, committee, assistant）
            $table->string('display_name'); // 表示用の名前（管理者、競技委員、補佐員）
            $table->text('description')->nullable(); // 役割の説明
            $table->boolean('is_active')->default(true); // アクティブフラグ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
