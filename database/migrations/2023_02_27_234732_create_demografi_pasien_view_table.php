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

        $sqlSelect = <<<SQL
            kecamatan.nm_kec as kecamatan,
            reg_periksa.no_rkm_medis as no_rm,
            reg_periksa.no_rawat as no_rawat,
            pasien.nm_pasien as nm_pasien,
            pasien.alamat as almt,
            reg_periksa.umurdaftar as umurdaftar,
            reg_periksa.sttsumur as sttsumur,
            reg_periksa.tgl_registrasi as tgl_registrasi,
            concat(reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur) as umur,
            pasien.jk as jk,
            penyakit.nm_penyakit as diagnosa,
            pasien.agama as agama,
            pasien.pnd as pendidikan,
            bahasa_pasien.nama_bahasa as bahasa,
            suku_bangsa.nama_suku_bangsa as suku
        SQL;

        $query = DB::connection('mysql_sik')
            ->table("{$db}.reg_periksa", 'reg_periksa')
            ->selectRaw($sqlSelect)
            ->join(DB::raw("{$db}.pasien pasien"), "reg_periksa.no_rkm_medis", '=', "pasien.no_rkm_medis")
            ->leftJoin(DB::raw("{$db}.kecamatan kecamatan"), "pasien.kd_kec", '=', "kecamatan.kd_kec")
            ->leftJoin(DB::raw("{$db}.bahasa_pasien bahasa_pasien"), "pasien.bahasa_pasien", '=', "bahasa_pasien.id")
            ->leftJoin(DB::raw("{$db}.suku_bangsa suku_bangsa"), "pasien.suku_bangsa", '=', "suku_bangsa.id")
            ->leftJoin(DB::raw("{$db}.diagnosa_pasien diagnosa_pasien"), "reg_periksa.no_rawat", '=', "diagnosa_pasien.no_rawat")
            ->leftJoin(DB::raw("{$db}.penyakit penyakit"), "diagnosa_pasien.kd_penyakit", '=', "penyakit.kd_penyakit")
            ->whereNotIn("reg_periksa.stts", ['batal', 'belum'])
            ->groupBy([
                "kecamatan.nm_kec",
                "reg_periksa.no_rkm_medis",
                "reg_periksa.no_rawat",
            ]);

        Schema::connection('mysql_smc')->createOrReplaceView('demografi_pasien', $query);
    }
};
