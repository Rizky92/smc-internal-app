<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('faktur_pajak_ditarik', function (Blueprint $table): void {
            $table->id();
            
            $table->string('no_rawat', 17)->index();
            $table->char('kode_transaksi', 4)->index()->default('');
            $table->date('tgl_bayar')->index();
            $table->time('jam_bayar')->index();
            $table->dateTime('tgl_tarikan')->index();
            
            $table->string('jenis_faktur', 30)->default('Normal');
            $table->string('keterangan_tambahan', 200)->nullable();
            $table->string('dokumen_pendukung', 200)->nullable();
            $table->string('cap_fasilitas', 100)->nullable();
            $table->string('id_tku_penjual', 30);
            $table->string('npwp_nik', 30)->default('');
            $table->string('jenis_id', 10)->default('National ID');
            $table->char('negara', 3)->default('IDN');
            $table->string('nomor_dokumen', 30)->default('');
            $table->string('nama', 100);
            $table->string('alamat', 400);
            $table->string('email', 50)->nullable();
            $table->string('no_tlp', 40)->nullable();
            $table->string('id_tku', 30)->nullable();
        });
    }
};
