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
            reg_periksa.no_rawat,
            reg_periksa.no_rkm_medis,
            pasien.nm_pasien,
            pasien.no_ktp,
            pasien.jk,
            pasien.tgl_lahir,
            concat(reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur) as umur,
            pasien.agama,
            suku_bangsa.nama_suku_bangsa,
            reg_periksa.status_lanjut,
            reg_periksa.status_poli,
            poliklinik.nm_poli,
            dokter.nm_dokter,
            reg_periksa.stts,
            reg_periksa.tgl_registrasi,
            reg_periksa.jam_reg,
            if(rawat_inap.tgl_keluar > '0000-00-00', rawat_inap.tgl_keluar, '-') as tgl_keluar,
            if(rawat_inap.jam_keluar > '00:00:00', rawat_inap.jam_keluar, '-') as jam_keluar,
            rawat_inap.diagnosa_awal,
            group_concat(distinct diagnosa.kd_penyakit separator ', ') as kd_diagnosa,
            group_concat(distinct diagnosa.nm_penyakit separator ', ') as nm_diagnosa,
            group_concat(distinct tindakan_ralan.kd_jenis_prw separator ', ') as kd_tindakan_ralan,
            group_concat(distinct jns_perawatan.nm_perawatan separator ', ') as nm_tindakan_ralan,
            group_concat(distinct tindakan_ranap.kd_jenis_prw separator ', ') as kd_tindakan_ranap,
            group_concat(distinct jns_perawatan_inap.nm_perawatan separator ', ') as nm_tindakan_ranap,
            '-' as lama_operasi,
            '-' as rujukan_masuk,
            group_concat(distinct dpjp.dokter_ranap separator '; ') as dokter_pj,
            kamar.kelas,
            penjab.png_jawab,
            reg_periksa.status_bayar as status_bayar,
            rawat_inap.stts_pulang as status_pulang_ranap,
            rujuk.rujuk_ke as rujuk_keluar_rs,
            pasien.alamat,
            pasien.no_tlp as no_hp,
            (
                select count(rp2.no_rawat)
                from $db.reg_periksa rp2
                where rp2.no_rkm_medis = reg_periksa.no_rkm_medis and rp2.tgl_registrasi <= reg_periksa.tgl_registrasi
            ) as kunjungan_ke
        from $db.reg_periksa reg_periksa
        join $db.pasien pasien on reg_periksa.no_rkm_medis = pasien.no_rkm_medis
        left join $db.suku_bangsa suku_bangsa on pasien.suku_bangsa = suku_bangsa.id
        left join $db.poliklinik poliklinik on reg_periksa.kd_poli = poliklinik.kd_poli
        left join $db.dokter dokter on reg_periksa.kd_dokter = dokter.kd_dokter
        left join $db.penjab penjab on reg_periksa.kd_pj = penjab.kd_pj
        left join (
            select
                kamar_inap.kd_kamar,
                kamar_inap.no_rawat,
                kamar_inap.diagnosa_awal,
                kamar_inap.tgl_keluar,
                kamar_inap.jam_keluar,
                kamar_inap.lama,
                kamar_inap.stts_pulang
            from $db.kamar_inap kamar_inap
            where kamar_inap.stts_pulang != 'pindah kamar'
            order by kamar_inap.no_rawat desc
        ) rawat_inap on reg_periksa.no_rawat = rawat_inap.no_rawat
        left join (
            select dpjp_ranap.no_rawat, dokter.nm_dokter as dokter_ranap
            from $db.dpjp_ranap dpjp_ranap
            left join $db.dokter dokter on dpjp_ranap.kd_dokter = dokter.kd_dokter
            order by dpjp_ranap.no_rawat desc
        ) dpjp on rawat_inap.no_rawat = dpjp.no_rawat 
        left join $db.kamar kamar on rawat_inap.kd_kamar = kamar.kd_kamar
        left join $db.rujuk rujuk on reg_periksa.no_rawat = rujuk.no_rawat
        left join (
            select diagnosa_pasien.no_rawat, diagnosa_pasien.kd_penyakit, penyakit.nm_penyakit
            from $db.diagnosa_pasien diagnosa_pasien
            left join $db.penyakit penyakit on diagnosa_pasien.kd_penyakit = penyakit.kd_penyakit
        ) diagnosa on reg_periksa.no_rawat = diagnosa.no_rawat
        left join (
            select rawat_jl_dr.no_rawat, rawat_jl_dr.kd_jenis_prw
            from $db.rawat_jl_dr rawat_jl_dr
            union all
            select rawat_jl_drpr.no_rawat, rawat_jl_drpr.kd_jenis_prw
            from $db.rawat_jl_drpr rawat_jl_drpr
            union all
            select rawat_jl_pr.no_rawat, rawat_jl_pr.kd_jenis_prw
            from $db.rawat_jl_pr rawat_jl_pr
        ) tindakan_ralan on reg_periksa.no_rawat = tindakan_ralan.no_rawat
        left join $db.jns_perawatan jns_perawatan on tindakan_ralan.kd_jenis_prw = jns_perawatan.kd_jenis_prw
        left join (
            select rawat_inap_dr.no_rawat, rawat_inap_dr.kd_jenis_prw
            from $db.rawat_inap_dr rawat_inap_dr
            union all
            select rawat_inap_drpr.no_rawat, rawat_inap_drpr.kd_jenis_prw
            from $db.rawat_inap_drpr rawat_inap_drpr
            union all
            select rawat_inap_pr.no_rawat, rawat_inap_pr.kd_jenis_prw
            from $db.rawat_inap_pr rawat_inap_pr
        ) tindakan_ranap on reg_periksa.no_rawat = tindakan_ranap.no_rawat
        left join $db.jns_perawatan_inap jns_perawatan_inap on tindakan_ranap.kd_jenis_prw = jns_perawatan_inap.kd_jenis_prw
        group by reg_periksa.no_rawat;
        SQL;

        Schema::connection('mysql_smc')->createOrReplaceView('laporan_statistik', $query);
    }
};
