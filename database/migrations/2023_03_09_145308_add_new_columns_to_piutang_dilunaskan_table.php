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
        Schema::connection('mysql_smc')->table('piutang_dilunaskan', function (Blueprint $table) {
            $table->string('no_rkm_medis', 15)
                ->nullable()
                ->index()
                ->after('no_rawat');
                
            $table->string('nik_penagih', 20)
                ->nullable()
                ->index()
                ->after('nm_rek');

            $table->string('nik_menyetujui', 20)
                ->nullable()
                ->index()
                ->after('nik_penagih');

            $table->string('nik_validasi', 20)
                ->nullable()
                ->index()
                ->after('nik_menyetujui');
        });
    }
};
