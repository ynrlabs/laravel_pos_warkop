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
        Schema::create('shift_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->onDelete('cascade');
            $table->string('name', 50)->comment('Pagi, Siang, Malam, Full Day');
            $table->string('code', 10)->comment('PG, SG, ML, FD');
            $table->time('start_time')->comment('Jam buka shift');
            $table->time('end_time')->comment('Jam tutup shift');
            $table->boolean('is_overnight')->default(false)->comment('Shift melewati tengah malam');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['outlet_id', 'code'], 'uk_shift_def_outlet_code');
            $table->index('outlet_id', 'idx_shift_def_outlet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_definitions');
    }
};