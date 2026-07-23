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
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique()->comment('Kode outlet singkat, misal: WPK01');
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('npwp', 30)->nullable()->comment('NPWP untuk struk pajak');
            $table->string('logo_path', 255)->nullable();
            $table->text('receipt_footer')->nullable()->comment('Footer struk (promo, IG, dll)');
            $table->time('operational_start')->default('06:00:00');
            $table->time('operational_end')->default('22:00:00');
            $table->json('settings')->nullable()->comment('Setting outlet spesifik (JSON)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outlets');
    }
};
