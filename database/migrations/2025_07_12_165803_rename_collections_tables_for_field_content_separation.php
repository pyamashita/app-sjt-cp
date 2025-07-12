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
        // collection_contentsテーブルをcollection_fieldsにリネーム
        Schema::rename('collection_contents', 'collection_fields');
        
        // collection_dataテーブルをcollection_contentsにリネーム
        Schema::rename('collection_data', 'collection_contents');
        
        // collection_contentsテーブル（旧collection_data）のカラム調整
        Schema::table('collection_contents', function (Blueprint $table) {
            // content_id を field_id にリネーム
            $table->renameColumn('content_id', 'field_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // collection_contentsテーブルのカラム調整を戻す
        Schema::table('collection_contents', function (Blueprint $table) {
            $table->renameColumn('field_id', 'content_id');
        });
        
        // テーブル名を元に戻す
        Schema::rename('collection_contents', 'collection_data');
        Schema::rename('collection_fields', 'collection_contents');
    }
};
