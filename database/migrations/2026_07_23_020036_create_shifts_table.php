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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_definition_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict')->comment('Kasir yang buka shift');
            $table->date('shift_date')->comment('Tanggal shift');
            $table->enum('status', ['opened', 'closed', 'reconciled'])->default('opened');
            $table->timestamp('opened_at')->nullable()->comment('Waktu buka shift aktual');
            $table->timestamp('closed_at')->nullable()->comment('Waktu tutup shift aktual');
            $table->timestamp('reconciled_at')->nullable();
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable()->comment('Catatan shift');
            $table->timestamps();
            
            $table->unique(['outlet_id', 'shift_definition_id', 'shift_date', 'user_id'], 'uk_shifts_outlet_def_date_user');
            $table->index(['outlet_id', 'shift_date'], 'idx_shifts_outlet_date');
            $table->index('status', 'idx_shifts_status');
            $table->index('user_id', 'idx_shifts_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};