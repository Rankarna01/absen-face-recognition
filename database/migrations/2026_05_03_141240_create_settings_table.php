<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            // Identitas Sistem
            $table->string('app_name')->default('Family Market');
            $table->string('app_logo')->nullable();
            $table->text('app_address')->nullable();
            
            // Pengaturan Jam Kerja
            $table->time('default_jam_masuk')->default('08:00:00');
            $table->time('default_jam_pulang')->default('17:00:00');
            $table->integer('toleransi_keterlambatan')->default(15); // Dalam satuan menit
            
            // Pengaturan GPS Kantor (Default diletakkan di Medan sesuai domisili kita)
            $table->string('office_latitude')->default('3.595196'); 
            $table->string('office_longitude')->default('98.672226');
            $table->integer('office_radius')->default(50); // Radius absen dalam meter
            
            // Integrasi API Opsional
            $table->string('api_key')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
