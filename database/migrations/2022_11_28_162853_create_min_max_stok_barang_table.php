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
            $table->unsignedInteger('stok_min')->default(0);
            $table->unsignedInteger('stok_max')->default(0);
            $table->char('kode_suplier', 5)->nullable()->index();
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
        
    }
}
