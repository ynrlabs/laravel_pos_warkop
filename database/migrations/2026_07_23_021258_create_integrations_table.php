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
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->onDelete('cascade');
            $table->string('name', 50)->comment('midtrans, xendit, fonnte, wablas, whatsapp_business');
            $table->enum('type', ['payment_gateway', 'whatsapp_gateway', 'shipping', 'accounting', 'pos_sync']);
            $table->json('config')->comment('Encrypted sensitive data: {client_key, server_key, is_production: true}');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_primary')->default(false)->comment('Primary untuk tipe tersebut');
            $table->boolean('test_mode')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
            
            $table->unique(['outlet_id', 'name'], 'uk_integrations_outlet_name');
            $table->index('outlet_id', 'idx_integrations_outlet');
            $table->index('type', 'idx_integrations_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};