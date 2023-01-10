<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermintaanBatalRanapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_smc')->create('permintaan_batal_ranap', function (Blueprint $table) {
            $table->id();
            $table->string('no_rawat')->index();
            $table->string('kd_kamar')->index();
            $table->text('alasan_pembatalan');
            $table->string('petugas_yang_mengajukan')->index();
            $table->string('validator')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_smc')->dropIfExists('batal_ranap');
    }
}
