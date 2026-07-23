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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->onDelete('cascade');
            $table->string('name', 20)->comment('Nama unit: kg, gram, liter, ml, pcs, pack');
            $table->string('symbol', 10)->comment('Simbol: kg, g, L, ml, pcs');
            $table->enum('type', ['weight', 'volume', 'count'])->comment('Tipe unit untuk konversi');
            $table->decimal('base_conversion', 15, 6)->default(1)->comment('Konversi ke unit dasar (1 kg = 1000 g)');
            $table->boolean('is_base')->default(false)->comment('Unit dasar untuk tipe ini');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['outlet_id', 'name']);
            $table->unique(['outlet_id', 'symbol']);
            $table->index(['outlet_id', 'type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
