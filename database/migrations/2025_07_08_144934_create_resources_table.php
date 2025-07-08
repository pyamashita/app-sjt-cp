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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->bigInteger('size');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->string('category')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['is_public', 'category']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};