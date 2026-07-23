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
        Schema::create('tab_item_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tab_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('modifier_id')->constrained()->onDelete('restrict');
            $table->decimal('price_adjustment', 15, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['tab_item_id', 'modifier_id'], 'uk_tab_item_modifiers');
            $table->index('tab_item_id', 'idx_tim_tab_item');
            $table->index('modifier_id', 'idx_tim_modifier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tab_item_modifiers');
    }
};