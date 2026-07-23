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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('payment_number', 30)->comment('PAY-20260722-0001');
            $table->enum('method', [
                'cash', 
                'qris_dynamic', 
                'qris_static', 
                'ewallet_gopay', 
                'ewallet_shopeepay', 
                'ewallet_dana', 
                'ewallet_ovo', 
                'edc_debit', 
                'edc_credit', 
                'hutang', 
                'voucher', 
                'other'
            ]);
            $table->string('provider', 50)->nullable()->comment('midtrans, xendit, manual');
            $table->string('provider_transaction_id', 100)->nullable()->comment('ID transaksi dari gateway');
            $table->string('provider_reference', 100)->nullable()->comment('Reference number dari gateway');
            $table->decimal('amount', 15, 2)->comment('Jumlah pembayaran');
            $table->enum('status', ['pending', 'paid', 'failed', 'expired', 'refunded', 'cancelled'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable()->comment('Untuk QRIS dynamic (5 menit)');
            $table->text('qr_code_data')->nullable()->comment('QR code string untuk QRIS dynamic');
            $table->string('qr_image_path', 255)->nullable();
            $table->decimal('cash_received', 15, 2)->nullable()->comment('Uang diterima (untuk cash)');
            $table->decimal('cash_change', 15, 2)->nullable()->comment('Kembalian (untuk cash)');
            $table->string('ewallet_phone', 20)->nullable()->comment('Nomor HP untuk e-wallet');
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->unique(['outlet_id', 'payment_number'], 'uk_payments_outlet_number');
            $table->index('outlet_id', 'idx_payments_outlet');
            $table->index('order_id', 'idx_payments_order');
            $table->index('method', 'idx_payments_method');
            $table->index('status', 'idx_payments_status');
            $table->index('provider_transaction_id', 'idx_payments_provider_ref');
            $table->index('created_at', 'idx_payments_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};