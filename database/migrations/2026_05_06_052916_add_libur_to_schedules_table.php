<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->boolean('is_libur')->default(false)->after('shift_id');
            $table->string('keterangan_libur')->nullable()->after('is_libur');
            $table->unsignedBigInteger('shift_id')->nullable()->change(); // Mengizinkan shift_id kosong jika hari libur
        });
    }

    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['is_libur', 'keterangan_libur']);
        });
    }
};