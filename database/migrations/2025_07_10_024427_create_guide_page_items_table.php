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
        Schema::create('guide_page_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_page_group_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['resource', 'link']); // リソースまたはリンク
            $table->string('title');
            $table->string('url')->nullable(); // リンクの場合のURL
            $table->foreignId('resource_id')->nullable()->constrained()->nullOnDelete(); // リソースの場合
            $table->boolean('open_in_new_tab')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['guide_page_group_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guide_page_items');
    }
};
