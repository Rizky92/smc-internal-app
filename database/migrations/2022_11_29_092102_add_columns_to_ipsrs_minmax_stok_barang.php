<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToIpsrsMinmaxStokBarang extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_smc')->table('ipsrs_minmax_stok_barang', function (Blueprint $table) {
            $table->unsignedInteger('stok_min')->after('kode_brng');
            $table->char('kode_suplier', 5)->after('stok_max')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_smc')->table('ipsrs_minmax_stok_barang', function (Blueprint $table) {
            //
        });
    }
}
