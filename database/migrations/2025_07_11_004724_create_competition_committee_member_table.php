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
        Schema::create('competition_committee_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->onDelete('cascade');
            $table->foreignId('committee_member_id')->constrained()->onDelete('cascade');
            $table->string('role')->nullable()->comment('役割（競技主査、競技委員など）');
            $table->timestamps();
            
            // 複合ユニークキー（同じ大会に同じ競技委員は重複登録不可）
            $table->unique(['competition_id', 'committee_member_id']);
            
            // インデックス
            $table->index(['competition_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_committee_member');
    }
};
