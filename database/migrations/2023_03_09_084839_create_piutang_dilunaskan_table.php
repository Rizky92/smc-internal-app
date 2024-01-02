<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'mysql_smc';

    public function up(): void
    {
        Schema::connection('mysql_smc')->create('piutang_dilunaskan', function (Blueprint $table): void {
            $table->id();
            $table->string('no_jurnal', 20)->nullable()->index();
            $table->dateTime('waktu_jurnal')->nullable();
            $table->string('no_rawat', 17)->nullable()->index();
            $table->string('no_rkm_medis', 15)->nullable()->index();
            $table->string('no_tagihan', 20)->nullable()->index();
            $table->char('kd_pj', 3)->nullable()->index();
            $table->double('piutang_dibayar')->nullable();
            $table->date('tgl_penagihan')->nullable();
            $table->date('tgl_jatuh_tempo')->nullable();
            $table->date('tgl_bayar')->nullable();
            $table->enum('status', ['Bayar', 'Batal Bayar'])->nullable();
            $table->string('kd_rek', 15)->nullable()->index();
            $table->string('nm_rek', 100)->nullable();
            $table->string('nik_penagih', 20)->nullable()->index();
            $table->string('nik_menyetujui', 20)->nullable()->index();
            $table->string('nik_validasi', 20)->nullable()->index();
            $table->timestamps($precision = 6);
        });
    }
};
