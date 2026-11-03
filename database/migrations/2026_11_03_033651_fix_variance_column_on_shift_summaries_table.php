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
        Schema::table('shift_summaries', function (Blueprint $table) {
            $table->decimal('variance', 15, 2)->storedAs('actual_cash - expected_cash')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_summaries', function (Blueprint $table) {
            $table->decimal('variance', 15, 2)->default(0)->change();
        });
    }
};
