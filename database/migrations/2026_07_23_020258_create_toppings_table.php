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
        Schema::create('toppings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->onDelete('cascade');
            $table->string('name', 50);
            $table->string('slug', 60);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('unit', 20)->default('pcs')->comment('pcs, gram, ml, dll');
            $table->decimal('stock_qty', 10, 2)->default(0);
            $table->boolean('track_stock')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['outlet_id', 'slug']);
            $table->index(['outlet_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toppings');
    }
};
