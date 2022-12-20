<?php

namespace App\Models\Farmasi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;

class ResepObatRacikan extends Model
{
    protected $primaryKey = null;

    protected $table = 'obat_racikan';

    public $incrementing = false;

    public $timestamps = false;

    public const RALAN = 'ralan';

    public const RANAP = 'ranap';

    public function scopeKunjunganResepRacikanPasien(
        Builder $query,
        string $periodeAwal = '',
        string $periodeAkhir = '',
        string $jenisPerawatan = ''
    ): Builder
    {
        if (empty($periodeAwal)) {
            $periodeAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($periodeAkhir)) {
            $periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        return $query->selectRaw("
            obat_racikan.no_rawat,
            obat_racikan.tgl_perawatan,
            obat_racikan.jam,
            obat_racikan.nama_racik,
            obat_racikan.jml_dr,
            pasien.nm_pasien,
            reg_periksa.status_lanjut,
            round(sum(databarang.h_beli)) total_harga
        ")
            ->join('detail_obat_racikan', function (JoinClause $join) {
                $join->on('obat_racikan.tgl_perawatan', '=', 'detail_obat_racikan.tgl_perawatan')
                    ->on('obat_racikan.jam', '=', 'detail_obat_racikan.jam')
                    ->on('obat_racikan.no_rawat', '=', 'detail_obat_racikan.no_rawat')
                    ->on('obat_racikan.no_racik', '=', 'detail_obat_racikan.no_racik');
            })
            ->join('reg_periksa', 'obat_racikan.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('databarang', 'detail_obat_racikan.kode_brng', '=', 'databarang.kode_brng')
            ->where('reg_periksa.status_bayar', 'Sudah Bayar')
            ->whereBetween('obat_racikan.tgl_perawatan', [$periodeAwal, $periodeAkhir])
            ->when(!empty($jenisPerawatan), function (Builder $query) use ($jenisPerawatan) {
                return $query->where('reg_periksa.status_lanjut', $jenisPerawatan);
            })
            ->groupBy([
                'obat_racikan.no_rawat',
                'obat_racikan.tgl_perawatan',
                'obat_racikan.jam',
                'obat_racikan.nama_racik',
                'obat_racikan.jml_dr',
                'pasien.nm_pasien',
                'reg_periksa.status_lanjut',
            ]);
    }
}
