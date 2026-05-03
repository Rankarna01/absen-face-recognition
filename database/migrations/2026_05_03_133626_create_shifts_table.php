<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('nama_shift'); // Misal: Pagi, Siang, Malam, Libur
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->string('warna')->default('#F5A623'); // Untuk warna di kalender
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('shifts'); }
};