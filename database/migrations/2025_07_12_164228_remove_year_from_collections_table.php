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
        Schema::table('collections', function (Blueprint $table) {
            if (Schema::hasColumn('collections', 'year')) {
                $table->dropIndex(['year']); // インデックスを先に削除
                $table->dropColumn('year');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            if (!Schema::hasColumn('collections', 'year')) {
                $table->year('year')->nullable()->comment('大会年度');
                $table->index(['year']);
            }
        });
    }
};
