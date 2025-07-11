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
        // 既存データを保存するための一時カラムを追加
        Schema::table('users', function (Blueprint $table) {
            $table->string('role_temp')->nullable();
        });

        // 既存のroleデータを一時カラムにコピー
        DB::statement('UPDATE users SET role_temp = role');

        // 既存のroleカラムを削除
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        // 新しいrole_idカラムを追加（外部キー制約付き）
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->onDelete('restrict');
        });

        // データ移行: 文字列の役割をrole_idに変換
        $this->migrateRoleData();

        // 一時カラムを削除
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role_temp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 現在のrole_idを一時的に保存
        Schema::table('users', function (Blueprint $table) {
            $table->string('role_temp')->nullable();
        });

        // role_idから文字列に戻すデータ移行
        DB::statement("
            UPDATE users 
            SET role_temp = (
                SELECT CASE 
                    WHEN roles.name = 'admin' THEN '管理者'
                    WHEN roles.name = 'committee' THEN '競技委員'
                    WHEN roles.name = 'assistant' THEN '補佐員'
                    ELSE '補佐員'
                END
                FROM roles 
                WHERE roles.id = users.role_id
            )
        ");

        // role_idカラムを削除
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });

        // 元のroleカラムを復元
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('補佐員');
        });

        // データを戻す
        DB::statement('UPDATE users SET role = role_temp');

        // 一時カラムを削除
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role_temp');
        });
    }

    /**
     * 既存のroleデータをrole_idに移行
     */
    private function migrateRoleData(): void
    {
        // 管理者の移行
        DB::statement("
            UPDATE users 
            SET role_id = (SELECT id FROM roles WHERE name = 'admin') 
            WHERE role_temp = '管理者'
        ");

        // 競技委員の移行
        DB::statement("
            UPDATE users 
            SET role_id = (SELECT id FROM roles WHERE name = 'committee') 
            WHERE role_temp = '競技委員'
        ");

        // 補佐員の移行（デフォルト）
        DB::statement("
            UPDATE users 
            SET role_id = (SELECT id FROM roles WHERE name = 'assistant') 
            WHERE role_temp = '補佐員' OR role_temp IS NULL
        ");
    }
};
