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
        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredients')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->decimal('system_qty', 15, 4)->default(0);
            $table->decimal('physical_qty', 15, 4)->default(0);
            $table->decimal('variance_qty', 15, 4)->storedAs('`physical_qty` - `system_qty`');
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('variance_value', 15, 2)->storedAs('(`physical_qty` - `system_qty`) * `unit_cost`');
            $table->string('batch_number', 50)->nullable();
            $table->date('expired_at')->nullable();
            $table->string('notes', 255)->nullable();
            $table->foreignId('counted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('counted_at')->nullable();
            $table->timestamps();

            $table->index('opname_id', 'idx_opname_items_opname');
            $table->index('ingredient_id', 'idx_opname_items_ingredient');
            $table->index('product_id', 'idx_opname_items_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
    }
};
