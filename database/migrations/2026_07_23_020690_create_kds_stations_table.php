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
        Schema::create('kds_stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('outlets')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->enum('type', ['kitchen', 'barista', 'bar'])->default('kitchen');
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('color', 7)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->foreignId('printer_id')->nullable();
            $table->boolean('sound_enabled')->default(true);
            $table->string('sound_url')->nullable();
            $table->boolean('auto_print')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
            
            $table->unique(['outlet_id', 'slug']);
            $table->index(['outlet_id', 'is_active']);
            $table->index(['outlet_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kds_stations');
    }
};
