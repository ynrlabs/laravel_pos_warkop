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
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->string('opname_number', 30);
            $table->enum('type', ['full', 'partial', 'cycle'])->default('partial');
            $table->enum('status', ['draft', 'in_progress', 'completed', 'approved'])->default('draft');
            $table->date('opname_date');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['outlet_id', 'opname_number'], 'uk_opname_outlet_number');
            $table->index('outlet_id', 'idx_opname_outlet');
            $table->index('status', 'idx_opname_status');
            $table->index('opname_date', 'idx_opname_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
