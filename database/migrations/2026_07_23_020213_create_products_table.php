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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->string('image_path', 255)->nullable();
            $table->enum('type', ['finished_good', 'raw_material'])->default('finished_good')->comment('Produk jadi atau bahan baku');
            $table->decimal('base_price', 12, 2)->default(0)->comment('Harga dasar (tanpa varian)');
            $table->decimal('cost_price', 12, 2)->nullable()->comment('HPP per unit (auto calc dari recipe)');
            $table->integer('stock_qty')->default(0)->comment('Stok produk jadi (jika finished_good)');
            $table->integer('min_stock')->default(5)->comment('Minimum stok untuk alert');
            $table->integer('max_stock')->nullable()->comment('Maksimum stok (untuk PO suggestion)');
            $table->boolean('track_stock')->default(true)->comment('Track stok untuk produk jadi');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_bestseller')->default(false);
            $table->boolean('is_combo')->default(false)->comment('Apakah ini paket kombo');
            $table->integer('prep_time_minutes')->default(0)->comment('Estimasi waktu persiapan (menit)');
            $table->string('kds_station', 20)->nullable()->comment('Station KDS: barista/kitchen/both');
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable()->comment('Data tambahan (nutrition, allergen, dll)');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['outlet_id', 'category_id', 'is_active']);
            $table->index(['is_active', 'is_bestseller']);
            $table->index(['sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
