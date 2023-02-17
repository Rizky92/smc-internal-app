<?php

namespace App\Models\Perawatan;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RawatInap extends Model
{
    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'kamar_inap';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'trf_kamar',
        'lama',
        'ttl_biaya',
    ];

    public static $pivotColumns = [
        'trf_kamar',
        'diagnosa_awal',
        'diagnosa_akhir',
        'tgl_masuk',
        'jam_masuk',
        'tgl_keluar',
        'jam_keluar',
        'lama',
        'ttl_biaya',
        'stts_pulang',
    ];

    public function scopePiutangRanap(
        Builder $query,
        string $tglAwal = '',
        string $tglAkhir = '',
        string $status = 'Belum Lunas',
        string $jenisBayar = ''
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = "
            kamar_inap.no_rawat,
            reg_periksa.no_rkm_medis,
            pasien.nm_pasien,
            kamar_inap.tgl_keluar,
            penjab.png_jawab,
            kamar_inap.stts_pulang,
            kamar.kd_kamar,
            bangsal.nm_bangsal,
            piutang_pasien.uangmuka,
            piutang_pasien.totalpiutang
        ";

        return $query
            ->selectRaw($sqlSelect)
            ->join('reg_periksa', 'kamar_inap.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->join('kamar', 'kamar_inap.kd_kamar', '=', 'kamar.kd_kamar')
            ->join('bangsal', 'kamar.kd_bangsal', '=', 'bangsal.kd_bangsal')
            ->join('piutang_pasien', 'kamar_inap.no_rawat', '=', 'piutang_pasien.no_rawat')
            ->whereBetween('kamar_inap.tgl_keluar', [$tglAwal, $tglAkhir])
            ->where('piutang_pasien.status', $status)
            ->where('reg_periksa.kd_pj', $jenisBayar);
    }
}
