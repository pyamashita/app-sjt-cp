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
        Schema::create('resource_access_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('resources')->onDelete('cascade');
            $table->enum('type', ['ip_whitelist', 'token_required', 'time_limited']);
            $table->string('value'); // IP address, token, or time limit
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['resource_id', 'type', 'is_active']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_access_controls');
    }
};