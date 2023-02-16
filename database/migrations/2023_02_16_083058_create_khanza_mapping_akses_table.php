<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKhanzaMappingAksesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_smc')->create('khanza_mapping_akses', function (Blueprint $table) {
            $table->string('nama_field', 100);
            $table->string('judul_menu', 100);

            $table->index(['nama_field', 'judul_menu']);
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
