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
        Schema::create('shift_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->unique()->constrained()->onDelete('cascade');
            $table->decimal('opening_cash', 15, 2)->default(0)->comment('Modal awal dari shift_cash_details');
            $table->decimal('cash_sales', 15, 2)->default(0)->comment('Total penjualan tunai');
            $table->decimal('non_cash_sales', 15, 2)->default(0)->comment('Total non-tunai (QRIS, e-wallet, dll)');
            $table->decimal('refund_amount', 15, 2)->default(0)->comment('Total refund/void');
            $table->decimal('expected_cash', 15, 2)->default(0)->comment('Opening + Cash Sales - Refund');
            $table->decimal('actual_cash', 15, 2)->default(0)->comment('Hitung fisik dari closing details');
            $table->decimal('variance', 15, 2)->generatedAlwaysAs('actual_cash - expected_cash')->comment('Selisih (plus/minus)');
            $table->text('variance_notes')->nullable()->comment('Wajib diisi jika variance != 0');
            $table->integer('transaction_count')->default(0);
            $table->integer('void_count')->default(0);
            $table->integer('refund_count')->default(0);
            $table->timestamps();
            
            $table->index('shift_id', 'idx_shift_summary_shift');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_summaries');
    }
};