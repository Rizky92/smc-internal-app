<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_smc';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_smc')->create('template_hak_akses_detail', function (Blueprint $table) {
            $table->unsignedBigInteger('template_hak_akses_id')->index();
            $table->string('nama_field_khanza', 100)->index();

            $table->foreign('template_hak_akses_id')
                ->references('id')
                ->on('template_hak_akses');

            $table->foreign('nama_field_khanza')
                ->references('nama_field')
                ->on('khanza_mapping_akses');
        });
    }
};
