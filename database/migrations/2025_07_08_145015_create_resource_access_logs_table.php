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
        Schema::create('resource_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('resources')->onDelete('cascade');
            $table->foreignId('api_token_id')->nullable()->constrained('api_tokens')->onDelete('set null');
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->enum('action', ['view', 'download', 'denied']);
            $table->string('access_method')->nullable(); // 'token', 'ip_whitelist', 'public'
            $table->text('reason')->nullable(); // 拒否理由等
            $table->timestamp('accessed_at');
            $table->timestamps();
            
            $table->index(['resource_id', 'accessed_at']);
            $table->index(['ip_address', 'accessed_at']);
            $table->index('accessed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_access_logs');
    }
};