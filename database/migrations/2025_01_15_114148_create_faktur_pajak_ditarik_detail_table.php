<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('faktur_pajak_ditarik_detail', function (Blueprint $table): void {
            $table->id();
            $table->string('no_rawat', 17)->index();
            $table->char('kode_transaksi', 4)->index()->default('');
            $table->date('tgl_bayar')->index();
            $table->time('jam_bayar')->index();
            $table->dateTime('tgl_tarikan')->index();
            $table->string('menu', 10)->index();
            $table->enum('jenis_barang_jasa', ['A', 'B']);
            $table->string('kode_barang_jasa', 50);
            $table->string('nama_barang_jasa', 150)->comment('nm_perawatan');
            $table->string('nama_satuan_ukur', 15);
            $table->double('harga_satuan')->default(0)->comment('biaya_rawat');
            $table->double('jumlah_barang_jasa')->default(0)->comment('count(*)');
            $table->double('diskon_persen')->default(0)->comment('Persentase selisih dari diskon terhadap total billing pasien');
            $table->double('diskon_nominal')->default(0);
            $table->double('dpp')->default(0);
            $table->double('dpp_nilai_lain')->default(0);
            $table->double('ppn_persen')->default(0);
            $table->double('ppn_nominal')->default(0);
            $table->double('ppnbm_persen')->default(0);
            $table->double('ppnbm_nominal')->default(0);
            $table->string('kd_jenis_prw', 15)->index();
            $table->string('kategori', 30)->index();
            $table->string('status_lanjut', 10);
            $table->char('kode_asuransi', 4)->index()->comment('reg_periksa.kd_pj');
            $table->string('no_rkm_medis', 15)->comment('pasien.no_rkm_medis');
        });
    }
};
