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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('name', 50)->comment('Nama varian: Goreng/Rebus, Small/Medium/Large');
            $table->string('slug', 60);
            $table->decimal('price_adjustment', 10, 2)->default(0)->comment('Penambahan/pengurangan harga dari base_price');
            $table->integer('stock_qty')->default(0);
            $table->boolean('track_stock')->default(false);
            $table->boolean('is_default')->default(false)->comment('Varian default saat pilih produk');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['product_id', 'slug']);
            $table->index(['product_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
