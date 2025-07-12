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
        Schema::create('collection_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections')->onDelete('cascade')->comment('コレクションID');
            $table->string('name')->comment('コンテンツ名（半角英数字+記号）');
            $table->enum('content_type', ['string', 'text', 'boolean', 'resource', 'date', 'time'])->comment('コンテンツタイプ');
            $table->integer('max_length')->nullable()->comment('最大文字数（string/textの場合）');
            $table->boolean('is_required')->default(false)->comment('必須項目');
            $table->integer('sort_order')->default(0)->comment('表示順序');
            $table->timestamps();
            
            $table->unique(['collection_id', 'name']);
            $table->index(['collection_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_contents');
    }
};
