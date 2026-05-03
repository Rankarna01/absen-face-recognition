<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel users (Cascade: jika user dihapus, data employee ikut terhapus)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('jabatan')->nullable();
            $table->string('divisi')->nullable();
            $table->string('foto')->nullable();
            $table->longText('face_descriptor')->nullable(); // Disiapkan untuk data array wajah Face API
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};