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
            reg_periksa.no_rawat as no_rawat,
            reg_periksa.no_rkm_medis as no_rm,
            pasien.nm_pasien as pasien,
            pasien.no_ktp as nik,
            pasien.jk as jk,
            pasien.tgl_lahir as tgl_lahir,
            concat(reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur) as umur,
            pasien.agama as agama,
            suku_bangsa.nama_suku_bangsa as suku,
            reg_periksa.status_lanjut as status_rawat,
            reg_periksa.status_poli as status_poli,
            poliklinik.nm_poli as asal_poli,
            dokter.nm_dokter as dokter_poli,
            reg_periksa.stts as status_ralan,
            reg_periksa.tgl_registrasi as tgl_masuk,
            reg_periksa.jam_reg as jam_masuk,
            if(rawat_inap.tgl_keluar > '0000-00-00', rawat_inap.tgl_keluar, '-') as tgl_keluar,
            if(rawat_inap.jam_keluar > '00:00:00', rawat_inap.jam_keluar, '-') as jam_keluar,
            rawat_inap.diagnosa_awal as diagnosa_awal,
            group_concat(distinct diagnosa.kd_penyakit separator ', ') as kd_diagnosa,
            group_concat(distinct diagnosa.nm_penyakit separator ', ') as nm_diagnosa,
            group_concat(distinct tindakan_ralan.kd_jenis_prw separator ', ') as kd_tindakan_ralan,
            group_concat(distinct jns_perawatan.nm_perawatan separator ', ') as nm_tindakan_ralan,
            group_concat(distinct tindakan_ranap.kd_jenis_prw separator ', ') as kd_tindakan_ranap,
            group_concat(distinct jns_perawatan_inap.nm_perawatan separator ', ') as nm_tindakan_ranap,
            '-' as lama_operasi,
            '-' as rujukan_masuk,
            group_concat(distinct dpjp.dokter_ranap separator '; ') as dokter_pj,
            kamar.kelas as kelas,
            penjab.png_jawab as jenis_bayar,
            reg_periksa.status_bayar as status_bayar,
            rawat_inap.stts_pulang as status_pulang_ranap,
            rujuk.rujuk_ke as rujuk_keluar_rs,
            pasien.alamat as alamat,
            pasien.no_tlp as no_hp,
            (
                select count(reg_periksa2.no_rawat)
                from $db.reg_periksa reg_periksa2
                where reg_periksa2.no_rkm_medis = reg_periksa.no_rkm_medis
                    and reg_periksa2.tgl_registrasi <= reg_periksa.tgl_registrasi
            ) as kunjungan_ke
        SQL;

        $rawatInap = <<<SQL
            (
                select kamar_inap.kd_kamar as kd_kamar,
                    kamar_inap.no_rawat as no_rawat,
                    kamar_inap.diagnosa_awal as diagnosa_awal,
                    kamar_inap.tgl_keluar as tgl_keluar,
                    kamar_inap.jam_keluar as jam_keluar,
                    kamar_inap.lama as lama,
                    kamar_inap.stts_pulang as stts_pulang
                from $db.kamar_inap kamar_inap
                where kamar_inap.stts_pulang <> 'pindah kamar'
                order by kamar_inap.no_rawat desc
            ) rawat_inap
        SQL;

        $dpjp = <<<SQL
            (
                select
                    dpjp_ranap.no_rawat as no_rawat,
                    dokter.nm_dokter as dokter_ranap
                from $db.dpjp_ranap dpjp_ranap
                    left join $db.dokter dokter on dpjp_ranap.kd_dokter = dokter.kd_dokter
                order by dpjp_ranap.no_rawat desc
            ) dpjp
        SQL;

        $diagnosa = <<<SQL
            (
                select
                    diagnosa_pasien.no_rawat as no_rawat,
                    diagnosa_pasien.kd_penyakit as kd_penyakit,
                    penyakit.nm_penyakit as nm_penyakit
                from $db.diagnosa_pasien diagnosa_pasien
                    left join $db.penyakit penyakit on diagnosa_pasien.kd_penyakit = penyakit.kd_penyakit
            ) diagnosa
        SQL;

        $tindakanRalan = <<<SQL
            (
                select
                    rawat_jl_dr.no_rawat as no_rawat,
                    rawat_jl_dr.kd_jenis_prw as kd_jenis_prw
                from $db.rawat_jl_dr rawat_jl_dr
                union all
                select
                    rawat_jl_drpr.no_rawat as no_rawat,
                    rawat_jl_drpr.kd_jenis_prw as kd_jenis_prw
                from $db.rawat_jl_drpr rawat_jl_drpr
                union all
                select
                    rawat_jl_pr.no_rawat as no_rawat,
                    rawat_jl_pr.kd_jenis_prw as kd_jenis_prw
                from $db.rawat_jl_pr rawat_jl_pr
            ) tindakan_ralan
        SQL;

        $tindakanRanap = <<<SQL
            (
                select
                    rawat_inap_dr.no_rawat as no_rawat,
                    rawat_inap_dr.kd_jenis_prw as kd_jenis_prw
                from $db.rawat_inap_dr rawat_inap_dr
                union all
                select
                    rawat_inap_drpr.no_rawat as no_rawat,
                    rawat_inap_drpr.kd_jenis_prw as kd_jenis_prw
                from $db.rawat_inap_drpr rawat_inap_drpr
                union all
                select
                    rawat_inap_pr.no_rawat as no_rawat,
                    rawat_inap_pr.kd_jenis_prw as kd_jenis_prw
                from $db.rawat_inap_pr rawat_inap_pr
            ) tindakan_ranap
        SQL;

        $query = DB::connection('mysql_sik')
            ->table("{$db}.reg_periksa", 'reg_periksa')
            ->selectRaw($sqlSelect)
            ->join("{$db}.pasien", "{$db}.reg_periksa.no_rkm_medis", '=', "pasien.no_rkm_medis")
            ->leftJoin(DB::raw("{$db}.suku_bangsa suku_bangsa"), "pasien.suku_bangsa", '=', "suku_bangsa.id")
            ->leftJoin(DB::raw("{$db}.poliklinik poliklinik"), "reg_periksa.kd_poli", '=', "poliklinik.kd_poli")
            ->leftJoin(DB::raw("{$db}.dokter dokter"), "reg_periksa.kd_dokter", '=', "dokter.kd_dokter")
            ->leftJoin(DB::raw("{$db}.penjab penjab"), "reg_periksa.kd_pj", '=', "penjab.kd_pj")
            ->leftJoin(DB::raw($rawatInap), "reg_periksa.no_rawat", '=', 'rawat_inap.no_rawat')
            ->leftJoin(DB::raw("{$db}.kamar kamar"), 'rawat_inap.kd_kamar', '=', 'kamar.kd_kamar')
            ->leftJoin(DB::raw("{$db}.rujuk rujuk"), 'reg_periksa.no_rawat', '=', 'rujuk.no_rawat')
            ->leftJoin(DB::raw($dpjp), 'rawat_inap.no_rawat', '=', 'dpjp.no_rawat')
            ->leftJoin(DB::raw($diagnosa), "reg_periksa.no_rawat", '=', 'diagnosa.no_rawat')
            ->leftJoin(DB::raw($tindakanRalan), "reg_periksa.no_rawat", '=', 'tindakan_ralan.no_rawat')
            ->leftJoin(DB::raw("{$db}.jns_perawatan jns_perawatan"), 'tindakan_ralan.kd_jenis_prw', '=', "jns_perawatan.kd_jenis_prw")
            ->leftJoin(DB::raw($tindakanRanap), "reg_periksa.no_rawat", '=', 'tindakan_ranap.no_rawat')
            ->leftJoin(DB::raw("{$db}.jns_perawatan_inap jns_perawatan_inap"), 'tindakan_ranap.kd_jenis_prw', '=', "jns_perawatan_inap.kd_jenis_prw")
            ->groupBy('reg_periksa.no_rawat');

        Schema::connection('mysql_smc')->createOrReplaceView('laporan_statistik', $query);
    }
};
