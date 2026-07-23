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
        Schema::create('tab_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tab_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->foreignId('product_variant_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->comment('Harga saat order');
            $table->decimal('line_subtotal', 15, 2)->virtualAs('quantity * unit_price')->stored();
            $table->string('notes', 255)->nullable()->comment('Catatan khusus item');
            $table->enum('status', ['pending', 'sent_to_kds', 'making', 'ready', 'served', 'void'])->default('pending');
            $table->foreignId('kds_ticket_id')->nullable();
            $table->foreignId('added_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('added_at')->useCurrent();
            $table->timestamp('served_at')->nullable();
            $table->string('void_reason', 255)->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('voided_at')->nullable();
            $table->timestamps();
            
            $table->index('tab_id', 'idx_tab_items_tab');
            $table->index('product_id', 'idx_tab_items_product');
            $table->index('status', 'idx_tab_items_status');
            $table->index('kds_ticket_id', 'idx_tab_items_kds_ticket');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tab_items');
    }
};