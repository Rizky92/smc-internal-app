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
            $table->foreignId('faktur_pajak_ditarik_id')->constrained('faktur_pajak_ditarik');
            $table->enum('jenis_barang_jasa', ['A', 'B'])->index();
            $table->string('kode_barang_jasa', 50)->index();
            $table->string('nama_barang_jasa', 150)->index()->comment('nm_perawatan');
            $table->string('nama_satuan_ukur', 15)->index();
            $table->double('harga_satuan')->default(0)->comment('biaya_rawat');
            $table->double('jumlah_barang_jasa')->default(0)->comment('count(*)');
            $table->double('diskon_persen')->default(0)->comment('Persentase selisih dari diskon terhadap total billing pasien');
            $table->double('diskon_nominal')->default(0);
            $table->double('tambahan')->default(0);
            $table->double('dpp')->default(0);
            $table->double('dpp_nilai_lain')->default(0);
            $table->double('ppn_persen')->default(0);
            $table->double('ppn_nominal')->default(0);
            $table->double('total_ppn')->default(0);
            $table->double('ppnbm_persen')->default(0);
            $table->double('ppnbm_nominal')->default(0);
            $table->double('total_ppnbm')->default(0);
            $table->string('kd_jenis_prw', 15)->index();
            $table->string('kategori', 30)->index();
            $table->string('status_lanjut', 10);
        });
    }
};
