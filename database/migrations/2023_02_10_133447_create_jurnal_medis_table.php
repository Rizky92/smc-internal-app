<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJurnalMedisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_smc')->create('jurnal_medis', function (Blueprint $table) {
            $table->id();
            $table->string('no_jurnal', 20)->nullable();
            $table->timestamp('waktu_jurnal')->nullable();
            $table->string('no_faktur', 20)->nullable();
            $table->enum('status', ['Sudah', 'Batal'])->nullable();
            $table->text('ket')->nullable();
            $table->string('nik', 20)->nullable();

            $table->index(['no_jurnal', 'no_faktur', 'nik']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
