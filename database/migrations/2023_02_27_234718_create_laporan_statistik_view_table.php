<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateLaporanStatistikViewTable extends Migration
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
            $db.reg_periksa.no_rawat as no_rawat,
            $db.reg_periksa.no_rkm_medis as no_rm,
            $db.pasien.nm_pasien as pasien,
            $db.pasien.no_ktp as nik,
            $db.pasien.jk as jk,
            $db.pasien.tgl_lahir as tgl_lahir,
            concat($db.reg_periksa.umurdaftar, ' ', $db.reg_periksa.sttsumur) as umur,
            $db.pasien.agama as agama,
            $db.suku_bangsa.nama_suku_bangsa as suku,
            $db.reg_periksa.status_lanjut as status_rawat,
            $db.reg_periksa.status_poli as status_poli,
            $db.poliklinik.nm_poli as asal_poli,
            $db.dokter.nm_dokter as dokter_poli,
            $db.reg_periksa.stts as status_ralan,
            $db.reg_periksa.tgl_registrasi as tgl_masuk,
            $db.reg_periksa.jam_reg as jam_masuk,
            if(rawat_inap.tgl_keluar > '0000-00-00', rawat_inap.tgl_keluar, '-') as tgl_keluar,
            if(rawat_inap.jam_keluar > '00:00:00', rawat_inap.jam_keluar, '-') as jam_keluar,
            rawat_inap.diagnosa_awal as diagnosa_awal,
            group_concat(distinct diagnosa.kd_penyakit separator ', ') as kd_diagnosa,
            group_concat(distinct diagnosa.nm_penyakit separator ', ') as nm_diagnosa,
            group_concat(distinct tindakan_ralan.kd_jenis_prw separator ', ') as kd_tindakan_ralan,
            group_concat(distinct $db.jns_perawatan.nm_perawatan separator ', ') as nm_tindakan_ralan,
            group_concat(distinct tindakan_ranap.kd_jenis_prw separator ', ') as kd_tindakan_ranap,
            group_concat(distinct $db.jns_perawatan_inap.nm_perawatan separator ', ') as nm_tindakan_ranap,
            '-' as lama_operasi,
            '-' as rujukan_masuk,
            group_concat(distinct dpjp.dokter_ranap separator '; ') as dokter_pj,
            $db.kamar.kelas as kelas,
            $db.penjab.png_jawab as jenis_bayar,
            $db.reg_periksa.status_bayar as status_bayar,
            rawat_inap.stts_pulang as status_pulang_ranap,
            $db.rujuk.rujuk_ke as rujuk_keluar_rs,
            $db.pasien.alamat as alamat,
            $db.pasien.no_tlp as no_hp,
            (
                select count(reg_periksa2.no_rawat)
                from $db.reg_periksa reg_periksa2
                where reg_periksa2.no_rkm_medis = $db.reg_periksa.no_rkm_medis
                    and reg_periksa2.tgl_registrasi <= $db.reg_periksa.tgl_registrasi
            ) as kunjungan_ke
        SQL;

        $rawatInap = <<<SQL
            (
                select $db.kamar_inap.kd_kamar as kd_kamar,
                    $db.kamar_inap.no_rawat as no_rawat,
                    $db.kamar_inap.diagnosa_awal as diagnosa_awal,
                    $db.kamar_inap.tgl_keluar as tgl_keluar,
                    $db.kamar_inap.jam_keluar as jam_keluar,
                    $db.kamar_inap.lama as lama,
                    $db.kamar_inap.stts_pulang as stts_pulang
                from $db.kamar_inap
                where $db.kamar_inap.stts_pulang <> 'pindah kamar'
                order by $db.kamar_inap.no_rawat desc
            ) rawat_inap
        SQL;

        $dpjp = <<<SQL
            (
                select
                    $db.dpjp_ranap.no_rawat as no_rawat,
                    $db.dokter.nm_dokter as dokter_ranap
                from $db.dpjp_ranap
                    left join $db.dokter on $db.dpjp_ranap.kd_dokter = $db.dokter.kd_dokter
                order by $db.dpjp_ranap.no_rawat desc
            ) dpjp
        SQL;

        $diagnosa = <<<SQL
            (
                select
                    $db.diagnosa_pasien.no_rawat as no_rawat,
                    $db.diagnosa_pasien.kd_penyakit as kd_penyakit,
                    $db.penyakit.nm_penyakit as nm_penyakit
                from $db.diagnosa_pasien
                    left join $db.penyakit on $db.diagnosa_pasien.kd_penyakit = $db.penyakit.kd_penyakit
            ) diagnosa
        SQL;

        $tindakanRalan = <<<SQL
            (
                select
                    $db.rawat_jl_dr.no_rawat as no_rawat,
                    $db.rawat_jl_dr.kd_jenis_prw as kd_jenis_prw
                from $db.rawat_jl_dr
                union all
                select
                    $db.rawat_jl_drpr.no_rawat as no_rawat,
                    $db.rawat_jl_drpr.kd_jenis_prw as kd_jenis_prw
                from $db.rawat_jl_drpr
                union all
                select
                    $db.rawat_jl_pr.no_rawat as no_rawat,
                    $db.rawat_jl_pr.kd_jenis_prw as kd_jenis_prw
                from $db.rawat_jl_pr
            ) tindakan_ralan
        SQL;

        $tindakanRanap = <<<SQL
            (
                select
                    $db.rawat_inap_dr.no_rawat as no_rawat,
                    $db.rawat_inap_dr.kd_jenis_prw as kd_jenis_prw
                from $db.rawat_inap_dr
                union all
                select
                    $db.rawat_inap_drpr.no_rawat as no_rawat,
                    $db.rawat_inap_drpr.kd_jenis_prw as kd_jenis_prw
                from $db.rawat_inap_drpr
                union all
                select
                    $db.rawat_inap_pr.no_rawat as no_rawat,
                    $db.rawat_inap_pr.kd_jenis_prw as kd_jenis_prw
                from $db.rawat_inap_pr
            ) tindakan_ranap
        SQL;

        DB::connection('mysql_sik')
            ->table('reg_periksa')
            ->selectRaw($sqlSelect)
            ->join("{$db}.pasien", "{$db}.reg_periksa.no_rkm_medis", '=', "{$db}.pasien.no_rkm_medis")
            ->leftJoin("{$db}.suku_bangsa", "{$db}.pasien.suku_bangsa", '=', "{$db}.suku_bangsa.id")
            ->leftJoin("{$db}.poliklinik", "{$db}.reg_periksa.kd_poli", '=', "{$db}.poliklinik.kd_poli")
            ->leftJoin("{$db}.dokter", "{$db}.reg_periksa.kd_dokter", '=', "{$db}.dokter.kd_dokter")
            ->leftJoin("{$db}.penjab", "{$db}.reg_periksa.kd_pj", '=', "{$db}.penjab.kd_pj")
            ->leftJoin(DB::raw($rawatInap), "{$db}.reg_periksa.no_rawat", '=', 'rawat_inap.no_rawat')
            ->leftJoin(DB::raw($dpjp), 'rawat_inap.no_rawat', '=', 'dpjp.no_rawat')
            ->leftJoin(DB::raw($diagnosa), "{$db}.reg_periksa.no_rawat", '=', 'diagnosa.no_rawat')
            ->leftJoin(DB::raw($tindakanRalan), "{$db}.reg_periksa.no_rawat", '=', 'tindakan_ralan.no_rawat')
            ->leftJoin("{$db}.jns_perawatan", 'tindakan_ralan.kd_jenis_prw', '=', "{$db}.jns_perawatan.kd_jenis_prw")
            ->leftJoin(DB::raw($tindakanRanap), "{$db}.reg_periksa.no_rawat", '=', 'tindakan_ranap.no_rawat')
            ->leftJoin("{$db}.jns_perawatan_inap", 'tindakan_ranap.kd_jenis_prw', '=', "{$db}.jns_perawatan_inap.kd_jenis_prw")
            ->groupBy('{$db}.reg_periksa.no_rawat');

        Schema::connection('mysql_smc')->createView('laporan_statistik');
    }
}
