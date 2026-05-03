<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom role, default-nya kita set 'pegawai'
            $table->string('role', 20)->default('pegawai')->after('password');
            // Opsional: tambah NIP untuk pegawai nanti
            $table->string('nip', 50)->nullable()->unique()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'nip']);
        });
    }
};