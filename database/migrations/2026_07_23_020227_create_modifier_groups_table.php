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
        Schema::create('modifier_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->onDelete('cascade');
            $table->string('name', 50);
            $table->string('slug', 60);
            $table->enum('selection_type', ['single', 'multiple'])->default('single')->comment('single=radio, multiple=checkbox');
            $table->boolean('is_required')->default(false);
            $table->unsignedTinyInteger('min_selection')->default(0);
            $table->unsignedTinyInteger('max_selection')->nullable()->comment('Null = unlimited');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['outlet_id', 'slug']);
            $table->index(['outlet_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modifier_groups');
    }
};
