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
        Schema::create('tabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->constrained()->onDelete('restrict');
            $table->foreignId('table_id')->nullable()->constrained()->onDelete('set null')->comment('Meja (null jika antrian)');
            $table->foreignId('queue_id')->nullable()->constrained('queue_numbers')->onDelete('set null')->comment('Nomor antrian (null jika meja)');
            $table->string('customer_name', 100)->nullable()->comment('Nama pelanggan (optional)');
            $table->string('customer_phone', 20)->nullable();
            $table->integer('guest_count')->default(1);
            $table->enum('status', ['open', 'hold', 'paid', 'void', 'merged'])->default('open');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('service_charge_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('opened_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('merged_from_tab_id')->nullable()->constrained('tabs')->onDelete('set null')->comment('Jika tab digabung');
            $table->foreignId('merged_to_tab_id')->nullable()->constrained('tabs')->onDelete('set null')->comment('Tab tujuan penggabungan');
            $table->timestamps();
            
            $table->index('outlet_id', 'idx_tabs_outlet');
            $table->index('shift_id', 'idx_tabs_shift');
            $table->index('table_id', 'idx_tabs_table');
            $table->index('queue_id', 'idx_tabs_queue');
            $table->index('status', 'idx_tabs_status');
            $table->index('opened_at', 'idx_tabs_opened_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tabs');
    }
};