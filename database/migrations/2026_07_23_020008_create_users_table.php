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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->onDelete('restrict');
            $table->string('employee_id', 20)->unique()->nullable()->comment('NIK/ID Karyawan');
            $table->string('name', 100);
            $table->string('email', 100)->unique()->nullable()->comment('Optional, untuk admin/manager');
            $table->string('phone', 20)->nullable();
            $table->string('password', 255)->comment('Hashed password (bcrypt/argon2)');
            $table->string('pin', 255)->nullable()->comment('Hashed 4-6 digit PIN untuk aksi sensitif');
            $table->string('avatar_path', 255)->nullable();
            $table->enum('role', ['super_admin', 'manager', 'kasir', 'barista', 'koki', 'gudang'])->default('kasir');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->text('two_factor_secret')->nullable()->comment('Untuk 2FA manager/admin');
            $table->boolean('two_factor_enabled')->default(false);
            $table->json('preferences')->nullable()->comment('Preferensi UI (theme, sound, dll)');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['outlet_id']);
            $table->index(['role']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
