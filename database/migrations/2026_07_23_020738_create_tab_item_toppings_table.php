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
        Schema::create('tab_item_toppings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tab_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('topping_id')->constrained()->onDelete('restrict');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->comment('Harga topping saat order');
            $table->decimal('line_subtotal', 15, 2)->virtualAs('quantity * unit_price')->stored();
            $table->timestamps();
            
            $table->unique(['tab_item_id', 'topping_id'], 'uk_tab_item_toppings');
            $table->index('tab_item_id', 'idx_tit_tab_item');
            $table->index('topping_id', 'idx_tit_topping');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tab_item_toppings');
    }
};