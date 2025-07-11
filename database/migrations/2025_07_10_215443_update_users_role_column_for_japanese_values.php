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
        Schema::table('users', function (Blueprint $table) {
            // 既存のenum型roleカラムを削除
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            // 新しいstring型roleカラムを追加（日本語対応）
            $table->string('role', 20)->default('補佐員');
        });

        // 既存データの移行（英語から日本語へ）
        DB::statement("UPDATE users SET role = '管理者' WHERE role = 'admin'");
        DB::statement("UPDATE users SET role = '競技委員' WHERE role = 'competition_committee'");
        DB::statement("UPDATE users SET role = '補佐員' WHERE role = 'assistant'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 日本語データを英語に戻す
        DB::statement("UPDATE users SET role = 'admin' WHERE role = '管理者'");
        DB::statement("UPDATE users SET role = 'competition_committee' WHERE role = '競技委員'");
        DB::statement("UPDATE users SET role = 'assistant' WHERE role = '補佐員'");

        Schema::table('users', function (Blueprint $table) {
            // string型roleカラムを削除
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            // 元のenum型roleカラムを復元
            $table->enum('role', ['admin', 'competition_committee', 'assistant'])->default('assistant');
        });
    }
};
