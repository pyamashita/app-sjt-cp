<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ENUMに'scheduled'と'cancelled'を追加
        DB::statement("ALTER TABLE messages MODIFY COLUMN status ENUM('draft', 'scheduled', 'cancelled', 'pending', 'sending', 'sent', 'completed', 'partially_sent', 'failed') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 元のENUMに戻す
        DB::statement("ALTER TABLE messages MODIFY COLUMN status ENUM('draft', 'pending', 'sending', 'completed', 'failed') DEFAULT 'draft'");
    }
};
