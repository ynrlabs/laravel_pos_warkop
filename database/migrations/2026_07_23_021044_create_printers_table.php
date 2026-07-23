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
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('outlets')->onDelete('cascade');
            $table->string('name', 100);
            $table->enum('type', ['thermal', 'inkjet', 'dot_matrix'])->default('thermal');
            $table->enum('connection_type', ['bluetooth', 'usb', 'network', 'serial'])->default('network');
            $table->unsignedSmallInteger('paper_width')->default(80);
            $table->string('device_address')->nullable()->comment('Bluetooth MAC address');
            $table->string('ip_address', 45)->nullable();
            $table->unsignedSmallInteger('port')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('print_categories')->nullable()->comment('receipt, kitchen_ticket, barista_ticket, queue_ticket');
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['outlet_id', 'is_active']);
            $table->index(['outlet_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printers');
    }
};
