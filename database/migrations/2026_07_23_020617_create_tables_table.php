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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('outlets')->onDelete('cascade');
            $table->string('number', 20);
            $table->string('label')->nullable(); // e.g., "Meja 1", "Teras 1"
            $table->unsignedSmallInteger('capacity')->default(4);
            $table->enum('status', ['available', 'occupied', 'reserved', 'maintenance'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->string('location')->nullable()->comment('Indoor, Outdoor, VIP, etc');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
            
            $table->unique(['outlet_id', 'number']);
            $table->index(['outlet_id', 'status']);
            $table->index(['outlet_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
