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
        Schema::create('po_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')->constrained('po_receipts')->cascadeOnDelete();
            $table->foreignId('po_item_id')->constrained('po_items')->restrictOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->decimal('quantity_received', 15, 4);
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('unit_cost', 15, 4);
            $table->string('batch_number', 50)->nullable();
            $table->date('expired_at')->nullable();
            $table->enum('quality_status', ['good', 'damaged', 'expired', 'return'])->default('good');
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index('receipt_id', 'idx_pri_receipt');
            $table->index('po_item_id', 'idx_pri_po_item');
            $table->index('ingredient_id', 'idx_pri_ingredient');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('po_receipt_items');
    }
};
