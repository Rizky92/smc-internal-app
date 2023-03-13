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
            $table->foreignId('template_hak_akses_id')->constrained('template_hak_akses');
            $table->foreignId('nama_field_khanza')->constrained('khanza_mapping_akses', 'nama_field');
        });
    }
};
