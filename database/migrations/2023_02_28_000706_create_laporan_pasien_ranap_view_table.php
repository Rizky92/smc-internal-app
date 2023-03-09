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
            reg_periksa.no_rawat,
            reg_periksa.tgl_registrasi,
            reg_periksa.jam_reg,
            kamar.kelas,
            concat(kamar.kd_kamar, ' ', bangsal.nm_bangsal) as ruangan,
            kamar_inap.trf_kamar,
            reg_periksa.no_rkm_medis,
            concat(pasien.nm_pasien, ' (', reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur, ')') as data_pasien,
            penjab.png_jawab,
            poliklinik.nm_poli,
            dokter.nm_dokter,
            kamar_inap.stts_pulang,
            kamar_inap.tgl_masuk,
            kamar_inap.jam_masuk,
            if(kamar_inap.tgl_keluar = '0000-00-00', '-', kamar_inap.tgl_keluar) as tgl_keluar,
            if(kamar_inap.jam_keluar = '00:00:00', '-', kamar_inap.jam_keluar) as jam_keluar,
            group_concat(dokter_pj.nm_dokter separator ', ') as dpjp,
            case
                when timestamp(kamar_inap.tgl_masuk, kamar_inap.jam_masuk) = kamar_inap_min.waktu_masuk and kamar_inap.stts_pulang <> 'pindah kamar' then 1
                when timestamp(kamar_inap.tgl_masuk, kamar_inap.jam_masuk) = kamar_inap_min.waktu_masuk and kamar_inap.stts_pulang = 'pindah kamar' then 2
                when timestamp(kamar_inap.tgl_masuk, kamar_inap.jam_masuk) >= kamar_inap_min.waktu_masuk then 3
            end as status_ranap
        SQL;

        $sqlGroupBy = <<<SQL
            reg_periksa.no_rawat,
            kamar_inap.tgl_masuk,
            kamar_inap.jam_masuk,
            kamar_inap.kd_kamar,
            kamar_inap.tgl_keluar,
            kamar_inap.jam_keluar;
        SQL;

        $kamarInapMin = DB::raw("(
            select
                kamar_inap2.no_rawat as no_rawat,
                kamar_inap2.stts_pulang as stts_pulang,
                min(timestamp(kamar_inap2.tgl_masuk, kamar_inap2.jam_masuk)) as waktu_masuk
            from {$db}.kamar_inap kamar_inap2
            group by kamar_inap2.no_rawat
        ) kamar_inap_min");

        $query = DB::connection('mysql_sik')
            ->table("{$db}.reg_periksa", 'reg_periksa')
            ->selectRaw($sqlSelect)
            ->join(DB::raw("{$db}.kamar_inap kamar_inap"), 'reg_periksa.no_rawat', '=', 'kamar_inap.no_rawat')
            ->join(DB::raw("{$db}.kamar kamar"), 'kamar_inap.kd_kamar', '=', 'kamar.kd_kamar')
            ->join(DB::raw("{$db}.bangsal bangsal"), 'kamar.kd_bangsal', '=', 'bangsal.kd_bangsal')
            ->join(DB::raw("{$db}.pasien pasien"), 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join(DB::raw("{$db}.penjab penjab"), 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->join(DB::raw("{$db}.dokter dokter"), 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->leftJoin(DB::raw("{$db}.dpjp_ranap dpjp_ranap"), 'kamar_inap.no_rawat', '=', 'dpjp_ranap.no_rawat')
            ->leftJoin(DB::raw("{$db}.dokter dokter_pj"), 'dpjp_ranap.kd_dokter', '=', 'dokter_pj.kd_dokter')
            ->leftJoin(DB::raw("{$db}.poliklinik poliklinik"), 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->join($kamarInapMin, 'kamar_inap.no_rawat', '=', 'kamar_inap_min.no_rawat')
            ->groupByRaw($sqlGroupBy);
            
        Schema::connection('mysql_smc')->createOrReplaceView('laporan_pasien_ranap', $query);
    }
};
