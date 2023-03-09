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
            $db.kecamatan.nm_kec as kecamatan,
            $db.reg_periksa.no_rkm_medis as no_rm,
            $db.reg_periksa.no_rawat as no_rawat,
            $db.pasien.nm_pasien as nm_pasien,
            $db.pasien.alamat as almt,
            $db.reg_periksa.umurdaftar as umurdaftar,
            $db.reg_periksa.sttsumur as sttsumur,
            $db.reg_periksa.tgl_registrasi as tgl_registrasi,
            concat($db.reg_periksa.umurdaftar, ' ', $db.reg_periksa.sttsumur) as umur,
            $db.pasien.jk as jk,
            $db.penyakit.nm_penyakit as diagnosa,
            $db.pasien.agama as agama,
            $db.pasien.pnd as pendidikan,
            $db.bahasa_pasien.nama_bahasa as bahasa,
            $db.suku_bangsa.nama_suku_bangsa as suku
        SQL;

        $query = DB::connection('mysql_sik')
            ->table('reg_periksa')
            ->selectRaw($sqlSelect)
            ->join("{$db}.pasien", "{$db}.reg_periksa.no_rkm_medis", '=', "{$db}.pasien.no_rkm_medis")
            ->leftJoin("{$db}.kecamatan", "{$db}.pasien.kd_kec", '=', "{$db}.kecamatan.kd_kec")
            ->leftJoin("{$db}.bahasa_pasien", "{$db}.pasien.bahasa_pasien", '=', "{$db}.bahasa_pasien.id")
            ->leftJoin("{$db}.suku_bangsa", "{$db}.pasien.suk_bangsa", '=', "{$db}.suku_bangsa.id")
            ->leftJoin("{$db}.diagnosa_pasien", "{$db}.reg_periksa.no_rawat", '=', "{$db}.diagnosa_pasien.no_rawat")
            ->leftJoin("{$db}.penyakit", "{$db}.diagnosa_pasien.kd_penyakit", '=', "{$db}.penyakit.kd_penyakit")
            ->whereNotIn("{$db}.reg_periksa.stts", ['batal', 'belum'])
            ->groupBy([
                "{$db}.kecamatan.nm_kec",
                "{$db}.reg_periksa.no_rkm_medis",
                "{$db}.reg_periksa.no_rawat",
            ]);

        Schema::connection('mysql_smc')->createView('demografi_pasien', $query);
    }
};
