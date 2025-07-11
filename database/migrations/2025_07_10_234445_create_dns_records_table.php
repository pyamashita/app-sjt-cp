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
        Schema::create('dns_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained('servers')->onDelete('cascade')->comment('所属するDNSサーバーID');
            $table->string('name')->comment('レコード名');
            $table->enum('type', ['A', 'AAAA', 'CNAME', 'MX', 'TXT', 'PTR', 'SRV', 'NS', 'SOA'])->comment('レコードタイプ');
            $table->text('value')->comment('レコード値');
            $table->integer('ttl')->default(3600)->comment('TTL（秒）');
            $table->integer('priority')->nullable()->comment('優先度（MX、SRVレコード用）');
            $table->text('description')->nullable()->comment('説明');
            $table->boolean('is_active')->default(true)->comment('アクティブ状態');
            $table->timestamps();
            
            // インデックス
            $table->index(['server_id', 'type', 'is_active']);
            $table->index(['name', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dns_records');
    }
};
