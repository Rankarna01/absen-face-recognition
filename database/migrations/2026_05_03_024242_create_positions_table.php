<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel divisions (Cascade: Jika divisi dihapus, jabatannya ikut terhapus)
            $table->foreignId('division_id')->constrained('divisions')->onDelete('cascade');
            $table->string('nama_jabatan');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('positions'); }
};