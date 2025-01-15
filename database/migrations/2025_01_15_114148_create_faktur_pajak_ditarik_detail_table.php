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
            $table->string('kode_barang_jasa', 50)->index();
            $table->string('nama_barang_jasa', 150)->index();
            $table->string('nama_satuan_ukur', 15);
            $table->string('jenis_item', 10)->index();
            $table->string('kategori_item', 30)->index();
            $table->string('status_lanjut', 10);
            $table->double('harga_satuan');
            $table->double('jumlah_barang_jasa');
            $table->double('diskon');
            $table->double('tambahan');
            $table->double('dpp');
            $table->double('dpp_nilai_lain');
            $table->double('ppn_persen');
            $table->double('total_ppn');
            $table->double('ppnbm_persen');
            $table->double('total_ppnbm');

            $table->foreignId('nama_satuan_pajak')
                ->references('kode_satuan_pajak')
                ->on('satuan_ukuran_pajak');
        });
    }
};
