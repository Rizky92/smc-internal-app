<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMinMaxStokBarangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_smc')->create('ipsrs_minmax_stok_barang', function (Blueprint $table) {
            $table->string('kode_brng', 15)->primary();
            $table->unsignedInteger('stok_max')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by', 700)->nullable();
            $table->string('updated_by', 700)->nullable();
            $table->string('deleted_by', 700)->nullable();

            $table->index(['created_by', 'updated_by', 'deleted_by']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_smc')->dropIfExists('ipsrs_minmax_stok_barang');
    }
}
