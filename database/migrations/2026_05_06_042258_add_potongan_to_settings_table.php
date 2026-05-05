<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('settings', function (Blueprint $table) {
        $table->integer('nominal_potongan_telat')->default(0)->after('toleransi_keterlambatan');
        $table->integer('nominal_potongan_alfa')->default(0)->after('nominal_potongan_telat');
    });
}
public function down()
{
    Schema::table('settings', function (Blueprint $table) {
        $table->dropColumn(['nominal_potongan_telat', 'nominal_potongan_alfa']);
    });
}
};
