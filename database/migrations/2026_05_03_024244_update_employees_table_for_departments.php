<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('employees', function (Blueprint $table) {
            // Hapus kolom lama
            $table->dropColumn(['jabatan', 'divisi']);
            // Tambah relasi baru
            $table->foreignId('division_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropForeign(['position_id']);
            $table->dropColumn(['division_id', 'position_id']);
            $table->string('jabatan')->nullable();
            $table->string('divisi')->nullable();
        });
    }
};