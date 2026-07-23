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
        Schema::create('shift_cash_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['opening', 'closing'])->comment('Buka kasir / Tutup kasir');
            $table->decimal('denomination', 15, 2)->comment('Nominal pecahan (100000, 50000, 20000, 10000, 5000, 2000, 1000, 500, 200, 100)');
            $table->integer('quantity')->default(0)->comment('Jumlah lembar/koin');
            $table->decimal('subtotal', 15, 2)->virtualAs('denomination * quantity')->stored();
            $table->string('notes', 255)->nullable();
            $table->timestamps();
            
            $table->unique(['shift_id', 'type', 'denomination'], 'uk_shift_cash_shift_type_denom');
            $table->index('shift_id', 'idx_shift_cash_shift');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_cash_details');
    }
};