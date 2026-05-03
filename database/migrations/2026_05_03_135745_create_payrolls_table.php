<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('periode'); // Format: YYYY-MM (Contoh: 2026-04)
            
            // Pemasukan
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->decimal('tunjangan', 15, 2)->default(0);
            $table->decimal('bonus', 15, 2)->default(0);
            
            // Potongan (Otomatis & Manual)
            $table->integer('jumlah_telat')->default(0); // Diambil otomatis dari absensi
            $table->decimal('potongan_telat', 15, 2)->default(0); // jumlah_telat * nominal denda
            
            // Total
            $table->decimal('total_bersih', 15, 2)->default(0);
            $table->enum('status', ['draft', 'dibayar'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};