<?php

namespace App\Models\Casemix;

use App\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class BridgingSep extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'bridging_sep';

    protected $primaryKey = 'no_sep';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * @psalm-return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function scopePasienBatal(Builder $query, string $tglAwal, string $tglAkhir): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $this->addSearchConditions([
            'bridging_sep.no_sep',
            'bridging_sep.no_rawat',
            'bridging_sep.tglsep',
            'reg_periksa.status_lanjut',
            'reg_periksa.no_rkm_medis',
            'pasien.nm_pasien',
            'reg_periksa.status_bayar',
            'reg_periksa.stts',
            'penjab.png_jawab',
        ]);

        $sqlSelect = <<<'SQL'
            bridging_sep.no_sep no_sep,
            bridging_sep.no_rawat no_rawat,
            bridging_sep.tglsep tglsep,
            IF(bridging_sep.jnspelayanan = '1', '1. Ranap', '2. Ralan') jenis_pelayanan,
            reg_periksa.status_lanjut status_lanjut,
            reg_periksa.no_rkm_medis no_rm,
            pasien.nm_pasien nama_pasien,
            reg_periksa.status_bayar status_bayar,
            reg_periksa.stts status_periksa,
            penjab.png_jawab jaminan_registrasi
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('reg_periksa', function ($join) {
                $join->on('bridging_sep.no_rawat', '=', 'reg_periksa.no_rawat')
                    ->whereRaw("IF(bridging_sep.jnspelayanan = '1', 'Ranap', 'Ralan') = reg_periksa.status_lanjut");
            })
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->whereBetween('bridging_sep.tglsep', [$tglAwal, $tglAkhir])
            ->where('reg_periksa.stts', 'Batal')
            ->orderBy('bridging_sep.no_sep');
    }

    /**
     * @psalm-return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function scopeRegistrasiCob(Builder $query, string $tglAwal, string $tglAkhir): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $this->addSearchConditions([
            'bridging_sep.no_sep',
            'bridging_sep.tglsep',
            'reg_periksa.no_rawat',
            'reg_periksa.no_rkm_medis',
            'pasien.nm_pasien',
            'penjab.png_jawab',
        ]);

        $sqlSelect = <<<'SQL'
            bridging_sep.no_sep no_sep,
            bridging_sep.tglsep tglsep,
            reg_periksa.no_rawat no_rawat,
            concat(bridging_sep.jnspelayanan, '. ', reg_periksa.status_lanjut) jenis_pelayanan,
            reg_periksa.no_rkm_medis no_rm,
            pasien.nm_pasien nama_pasien,
            penjab.png_jawab jaminan_registrasi
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('reg_periksa', function ($join) {
                $join->on('bridging_sep.no_rawat', '=', 'reg_periksa.no_rawat')
                    ->whereRaw("bridging_sep.jnspelayanan = IF(reg_periksa.status_lanjut = 'Ranap', '1', '2')");
            })
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->whereBetween('bridging_sep.tglsep', [$tglAwal, $tglAkhir])
            ->where('reg_periksa.kd_pj', '!=', 'BPJ');
    }

    /**
     * @psalm-return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function scopePasienRanapPulang(Builder $query, string $bulan): Builder
    {
        if (empty($bulan)) {
            $bulan = now()->subMonth()->format('Y-m');
        }

        $date = Carbon::parse($bulan.'-01');
        $prevMonthStart = $date->copy()->subMonth()->startOfMonth()->toDateString();
        $prevMonthEnd = $date->copy()->subMonth()->endOfMonth()->toDateString();
        $targetMonthStart = $date->startOfMonth()->format('Y-m%');
        $targetMonthEnd = $date->endOfMonth()->format('Y-m%');

        $this->addSearchConditions([
            'bridging_sep.no_sep',
            'bridging_sep.no_rawat',
            'reg_periksa.no_rkm_medis',
            'pasien.nm_pasien',
            'poliklinik.nm_poli',
            'penjab.png_jawab',
        ]);

        $sqlSelect = <<<'SQL'
            bridging_sep.no_sep no_sep,
            bridging_sep.no_rawat no_rawat,
            concat(reg_periksa.tgl_registrasi, ' ', reg_periksa.jam_reg) tgl_registrasi,
            reg_periksa.no_rkm_medis no_rm,
            pasien.nm_pasien nama_pasien,
            if(bridging_sep.jnspelayanan = '1', '1. Ranap', '2. Ralan') jenis_pelayanan,
            reg_periksa.status_lanjut status_lanjut,
            reg_periksa.kd_poli kd_poli,
            poliklinik.nm_poli nm_poli,
            reg_periksa.kd_pj kd_pj,
            penjab.png_jawab jenis_bayar,
            bridging_sep.klsrawat kls_rawat,
            bridging_sep.tglsep tglsep,
            if(bridging_sep.tglpulang = '0000-00-00 00:00:00', '', bridging_sep.tglpulang) tglpulang,
            ifnull((select concat(kamar_inap.tgl_masuk, ' ', kamar_inap.jam_masuk) from kamar_inap where kamar_inap.no_rawat = bridging_sep.no_rawat order by kamar_inap.tgl_masuk, kamar_inap.jam_masuk limit 1), '') tgl_masuk_ranap,
            ifnull((select if(kamar_inap.tgl_keluar = '0000-00-00', '', concat(kamar_inap.tgl_keluar, ' ', kamar_inap.jam_keluar)) from kamar_inap where kamar_inap.no_rawat = bridging_sep.no_rawat and kamar_inap.stts_pulang not in ('-', 'Pindah Kamar') order by kamar_inap.tgl_keluar desc, kamar_inap.jam_keluar desc limit 1), '') tgl_keluar_ranap,
            ifnull((select concat(kamar_inap.kd_kamar, ' ', bangsal.nm_bangsal) from kamar_inap inner join kamar on kamar_inap.kd_kamar = kamar.kd_kamar inner join bangsal on kamar.kd_bangsal = bangsal.kd_bangsal where kamar_inap.no_rawat = bridging_sep.no_rawat order by kamar_inap.tgl_keluar desc, kamar_inap.jam_keluar desc limit 1), '') kamar_terakhir,
            ifnull((select concat(nota_inap.tanggal, ' ', nota_inap.jam) from nota_inap where nota_inap.no_rawat = bridging_sep.no_rawat limit 1), 'Belum Bayar') tgl_close_billing
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('reg_periksa', 'bridging_sep.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->whereBetween('bridging_sep.tglsep', [$prevMonthStart, $prevMonthEnd])
            ->where('bridging_sep.jnspelayanan', '1')
            ->whereRaw('(exists(select * from nota_inap where nota_inap.no_rawat = bridging_sep.no_rawat and nota_inap.tanggal like ?) or not exists(select * from nota_inap where nota_inap.no_rawat = bridging_sep.no_rawat))', [$targetMonthStart])
            ->orderBy('bridging_sep.no_sep');
    }
}
