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
            $table->string('kode_item', 50)->index();
            $table->string('nama_item', 150)->index();
            $table->string('jenis_item', 10)->index();
            $table->string('kategori_item', 30)->index();
            $table->char('kode_sat', 4)->nullable();
            $table->string('namasatuan', 30)->nullable();
            $table->string('kode_satuan_pajak', 15);
            $table->string('status_lanjut', 10);
            $table->double('harga_satuan');
            $table->double('jumlah');
            $table->double('tambahan');
            $table->double('diskon');
            $table->double('total');

            $table->foreignId('kode_satuan_pajak')
                ->references('kode_satuan_pajak')
                ->on('satuan_ukuran_pajak');
        });
    }
};
