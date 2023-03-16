<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Staudenmeir\LaravelMigrationViews\Facades\Schema;

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
        $db = DB::connection('mysql_sik')->getDatabaseName();

        $query = <<<SQL
        select
            kecamatan.nm_kec as kecamatan,
            reg_periksa.no_rkm_medis as no_rm,
            reg_periksa.no_rawat as no_rawat,
            pasien.nm_pasien as nm_pasien,
            pasien.alamat as almt,
            reg_periksa.umurdaftar,
            reg_periksa.sttsumur,
            reg_periksa.tgl_registrasi,
            concat(reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur) as umur,
            pasien.jk,
            penyakit.nm_penyakit as diagnosa,
            pasien.agama,
            pasien.pnd as pendidikan,
            bahasa_pasien.nama_bahasa as bahasa,
            suku_bangsa.nama_suku_bangsa as suku
        from $db.reg_periksa reg_periksa
        join $db.pasien pasien on reg_periksa.no_rkm_medis = pasien.no_rkm_medis
        left join $db.kecamatan kecamatan on pasien.kd_kec = kecamatan.kd_kec
        left join $db.bahasa_pasien bahasa_pasien on pasien.bahasa_pasien = bahasa_pasien.id
        left join $db.suku_bangsa suku_bangsa on pasien.suku_bangsa = suku_bangsa.id
        left join $db.diagnosa_pasien diagnosa_pasien on reg_periksa.no_rawat = diagnosa_pasien.no_rawat
        left join $db.penyakit penyakit on diagnosa_pasien.kd_penyakit = penyakit.kd_penyakit
        where reg_periksa.stts not in ('batal', 'belum')
        group by
            kecamatan.nm_kec,
            reg_periksa.no_rkm_medis,
            reg_periksa.no_rawat
        order by reg_periksa.no_rawat;
        SQL;

        Schema::connection('mysql_smc')->createOrReplaceView('demografi_pasien', $query);
    }
};
