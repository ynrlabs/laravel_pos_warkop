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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->onDelete('cascade');
            $table->string('name', 50);
            $table->string('slug', 60)->unique();
            $table->string('icon', 50)->nullable()->comment('Lucide icon name atau emoji');
            $table->string('color', 7)->default('#8B4513')->comment('Hex color untuk badge');
            $table->integer('sort_order')->default(0)->comment('Urutan tampil di POS');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_kds_station')->default(false)->comment('Apakah kategori ini punya station KDS sendiri');
            $table->string('kds_station_name', 50)->nullable()->comment('Nama station KDS (Barista/Kitchen)');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['outlet_id', 'is_active']);
            $table->index(['sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
