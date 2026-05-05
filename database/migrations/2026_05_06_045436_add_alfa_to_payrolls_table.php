<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->integer('jumlah_alfa')->default(0)->after('potongan_telat');
            $table->integer('potongan_alfa')->default(0)->after('jumlah_alfa');
        });
    }

    public function down()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['jumlah_alfa', 'potongan_alfa']);
        });
    }
};