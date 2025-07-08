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
        // MySQLの場合、ENUM型の変更は特殊な処理が必要
        DB::statement("ALTER TABLE resource_access_controls MODIFY COLUMN type ENUM('ip_whitelist', 'api_token', 'token_required', 'time_limited') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 元のENUM値に戻す
        DB::statement("ALTER TABLE resource_access_controls MODIFY COLUMN type ENUM('ip_whitelist', 'token_required', 'time_limited') NOT NULL");
    }
};