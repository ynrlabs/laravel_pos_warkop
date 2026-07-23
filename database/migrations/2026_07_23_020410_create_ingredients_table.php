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
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->constrained()->onDelete('restrict')->comment('Unit dasar (base unit)');
            $table->string('name', 100);
            $table->string('slug', 120);
            $table->string('sku', 50)->nullable()->comment('SKU internal');
            $table->decimal('current_stock', 15, 4)->default(0)->comment('Stok saat ini dalam unit dasar');
            $table->decimal('min_stock', 15, 4)->default(0)->comment('Minimum stok untuk alert');
            $table->decimal('max_stock', 15, 4)->nullable()->comment('Maksimum stok untuk PO suggestion');
            $table->decimal('avg_cost', 12, 4)->default(0)->comment('Harga rata-rata (moving average)');
            $table->decimal('last_purchase_price', 12, 4)->nullable()->comment('Harga beli terakhir');
            $table->string('supplier_name', 100)->nullable()->comment('Preferred supplier');
            $table->integer('lead_time_days')->default(0)->comment('Lead time dari supplier (hari)');
            $table->boolean('track_stock')->default(true);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable()->comment('Info tambahan (kategori bahan, allergen, dll)');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['outlet_id', 'slug']);
            $table->index(['outlet_id', 'is_active']);
            $table->index(['current_stock']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
