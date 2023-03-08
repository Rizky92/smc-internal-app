<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateLaporanPasienRanapViewTable extends Migration
{
    protected $connection = 'mysql_smc';
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // select
        //     `sik`.`reg_periksa`.`no_rawat` as `no_rawat`,
        //     `sik`.`reg_periksa`.`tgl_registrasi` as `tgl_registrasi`,
        //     `sik`.`reg_periksa`.`jam_reg` as `jam_reg`,
        //     `sik`.`kamar`.`kelas` as `kelas`,
        //     concat(`sik`.`kamar`.`kd_kamar`, ' ', `sik`.`bangsal`.`nm_bangsal`) as `ruangan`,
        //     `sik`.`kamar_inap`.`trf_kamar` as `trf_kamar`,
        //     `sik`.`reg_periksa`.`no_rkm_medis` as `no_rkm_medis`,
        //     concat(`sik`.`pasien`.`nm_pasien`, ' (', `sik`.`reg_periksa`.`umurdaftar`, ' ', `sik`.`reg_periksa`.`sttsumur`, ')') as `data_pasien`,
        //     `sik`.`penjab`.`png_jawab` as `png_jawab`,
        //     `sik`.`poliklinik`.`nm_poli` as `nm_poli`,
        //     `sik`.`dokter`.`nm_dokter` as `dokter_poli`,
        //     `sik`.`kamar_inap`.`stts_pulang` as `stts_pulang`,
        //     `sik`.`kamar_inap`.`tgl_masuk` as `tgl_masuk`,
        //     `sik`.`kamar_inap`.`jam_masuk` as `jam_masuk`,
        //     if(
        //         `sik`.`kamar_inap`.`tgl_keluar` = '0000-00-00',
        //         '-',
        //         `sik`.`kamar_inap`.`tgl_keluar`
        //     ) as `tgl_keluar`,
        //     if(
        //         `sik`.`kamar_inap`.`jam_keluar` = '00:00:00',
        //         '-',
        //         `sik`.`kamar_inap`.`jam_keluar`
        //     ) as `jam_keluar`,
        //     group_concat(`dokter_pj`.`nm_dokter` separator ', ') as `dpjp`,
        //     case
        //         when timestamp(
        //             `sik`.`kamar_inap`.`tgl_masuk`,
        //             `sik`.`kamar_inap`.`jam_masuk`
        //         ) = `kamar_inap_min`.`waktu_masuk`
        //         and `sik`.`kamar_inap`.`stts_pulang` <> 'pindah kamar' then 1
        //         when timestamp(
        //             `sik`.`kamar_inap`.`tgl_masuk`,
        //             `sik`.`kamar_inap`.`jam_masuk`
        //         ) = `kamar_inap_min`.`waktu_masuk`
        //         and `sik`.`kamar_inap`.`stts_pulang` = 'pindah kamar' then 2
        //         when timestamp(
        //             `sik`.`kamar_inap`.`tgl_masuk`,
        //             `sik`.`kamar_inap`.`jam_masuk`
        //         ) >= `kamar_inap_min`.`waktu_masuk` then 3
        //     end as `status_ranap`
        // from
        //     (
        //         (
        //             (
        //                 (
        //                     (
        //                         (
        //                             (
        //                                 (
        //                                     (
        //                                         (
        //                                             `sik`.`reg_periksa`
        //                                         join `sik`.`kamar_inap` on
        //                                             (
        //                                                 `sik`.`reg_periksa`.`no_rawat` = `sik`.`kamar_inap`.`no_rawat`
        //                                             )
        //                                         )
        //                                     join `sik`.`kamar` on
        //                                         (
        //                                             `sik`.`kamar_inap`.`kd_kamar` = `sik`.`kamar`.`kd_kamar`
        //                                         )
        //                                     )
        //                                 join `sik`.`bangsal` on
        //                                     (
        //                                         `sik`.`kamar`.`kd_bangsal` = `sik`.`bangsal`.`kd_bangsal`
        //                                     )
        //                                 )
        //                             join `sik`.`pasien` on
        //                                 (
        //                                     `sik`.`reg_periksa`.`no_rkm_medis` = `sik`.`pasien`.`no_rkm_medis`
        //                                 )
        //                             )
        //                         join `sik`.`penjab` on
        //                             (
        //                                 `sik`.`reg_periksa`.`kd_pj` = `sik`.`penjab`.`kd_pj`
        //                             )
        //                         )
        //                     join `sik`.`dokter` on
        //                         (
        //                             `sik`.`reg_periksa`.`kd_dokter` = `sik`.`dokter`.`kd_dokter`
        //                         )
        //                     )
        //                 left join `sik`.`dpjp_ranap` on
        //                     (
        //                         `sik`.`kamar_inap`.`no_rawat` = `sik`.`dpjp_ranap`.`no_rawat`
        //                     )
        //                 )
        //             left join `sik`.`dokter` `dokter_pj` on
        //                 (
        //                     `sik`.`dpjp_ranap`.`kd_dokter` = `dokter_pj`.`kd_dokter`
        //                 )
        //             )
        //         join `sik`.`poliklinik` on
        //             (
        //                 `sik`.`reg_periksa`.`kd_poli` = `sik`.`poliklinik`.`kd_poli`
        //             )
        //         )
        //     join (
        //             select
        //                 `kamar_inap2`.`no_rawat` as `no_rawat`,
        //                 `kamar_inap2`.`stts_pulang` as `stts_pulang`,
        //                 min(timestamp(`kamar_inap2`.`tgl_masuk`, `kamar_inap2`.`jam_masuk`)) as `waktu_masuk`
        //             from
        //                 `sik`.`kamar_inap` `kamar_inap2`
        //             group by
        //                 `kamar_inap2`.`no_rawat`
        //         ) `kamar_inap_min` on
        //         (
        //             `sik`.`kamar_inap`.`no_rawat` = `kamar_inap_min`.`no_rawat`
        //         )
        //     )
        // group by
        //     `sik`.`reg_periksa`.`no_rawat`,
        //     `sik`.`kamar_inap`.`tgl_masuk`,
        //     `sik`.`kamar_inap`.`jam_masuk`,
        //     `sik`.`kamar_inap`.`kd_kamar`,
        //     `sik`.`kamar_inap`.`tgl_keluar`,
        //     `sik`.`kamar_inap`.`jam_keluar`;
        Schema::connection('mysql_smc')->createView('laporan_pasien_ranap');
    }
}
