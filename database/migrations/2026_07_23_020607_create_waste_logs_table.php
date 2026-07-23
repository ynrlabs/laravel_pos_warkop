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
        Schema::create('waste_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->string('waste_number', 30);
            $table->date('waste_date');
            $table->time('waste_time');
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredients')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->decimal('quantity', 15, 4);
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 2)->storedAs('`quantity` * `unit_cost`');
            $table->enum('reason', ['expired', 'spoiled', 'burnt', 'dropped', 'overcooked', 'wrong_order', 'sample', 'other']);
            $table->text('reason_detail')->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->foreignId('reported_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['outlet_id', 'waste_number'], 'uk_waste_outlet_number');
            $table->index('outlet_id', 'idx_waste_outlet');
            $table->index('waste_date', 'idx_waste_date');
            $table->index('ingredient_id', 'idx_waste_ingredient');
            $table->index('product_id', 'idx_waste_product');
            $table->index('status', 'idx_waste_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_logs');
    }
};
