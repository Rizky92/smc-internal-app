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
            $table->string('status_lanjut', 15)->index();
            $table->string('jenis_faktur', 30)->default('Normal');
            $table->string('keterangan_tambahan', 100)->nullable();
            $table->string('dokumen_pendukung', 100)->nullable();
            $table->string('cap_fasilitas', 100)->nullable();
            $table->string('id_tku_penjual', 30);
            $table->string('jenis_id', 20)->default('National ID');
            $table->char('negara', 3)->default('IDN');
            $table->string('id_tku', 30)->nullable();
            $table->string('no_rkm_medis', 15)->comment('pasien.no_rkm_medis');
            $table->string('nik_pasien', 20)->comment('pasien.no_ktp');
            $table->string('nama_pasien', 60)->commment('pasien.nm_pasien');
            $table->string('alamat_pasien', 400)->comment('concat_ws(\', \', pasien.alamat, kelurahan.nm_kel, kecamatan.nm_kec, kabupaten.nm_kab, propinsi.nm_prop)');
            $table->string('email_pasien', 50)->nullable()->comment('pasien.email');
            $table->string('no_telp_pasien', 40)->nullable()->comment('pasien.no_tlp');
            $table->char('kode_asuransi', 4)->index()->comment('reg_periksa.kd_pj / detail_piutang_pasien.kd_pj');
            $table->string('nama_asuransi', 50)->index()->comment('penjab.png_jawab');
            $table->string('alamat_asuransi', 150)->comment('penjab.alamat_asuransi');
            $table->string('telp_asuransi', 50)->nullable()->comment('penjab.no_telp');
            $table->string('email_asuransi', 40)->nullable()->comment('penjab.email');
            $table->string('npwp_asuransi', 30)->nullable()->comment('penjab.no_npwp');
            $table->string('kode_perusahaan', 8)->nullable()->index()->comment('pasien.perusahaan_pasien');
            $table->string('nama_perusahaan', 70)->nullable()->index()->comment('perusahaan_pasien.nama_perusahaan');
            $table->string('alamat_perusahaan', 150)->nullable()->comment('perusahaan_pasien.alamat');
            $table->string('telp_perusahaan', 40)->nullable()->comment('perusahaan_pasien.no_telp');
            $table->string('email_perusahaan', 50)->nullable()->comment('perusahaan_pasien.email');
            $table->string('npwp_perusahaan', 30)->nullable()->comment('perusahaan_pasien.no_npwp');
            $table->dateTime('tgl_tarikan')->index();
            $table->string('menu', 10)->index();
            $table->date('tgl_faktur');
        });
    }
};
