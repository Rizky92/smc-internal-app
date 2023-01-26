<?php

namespace App\Models\Farmasi;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ResepDokter extends Model
{
    use Searchable, Sortable;

    protected $primaryKey = 'no_resep';

    protected $keyType = 'string';

    protected $table = 'resep_dokter';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeKunjunganResepObatRegular(
        Builder $query,
        string $periodeAwal = '',
        string $periodeAkhir = '',
        string $jenisPerawatan = ''
    ): Builder {
        if (empty($periodeAwal)) {
            $periodeAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($periodeAkhir)) {
            $periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        return $query->selectRaw("
            resep_dokter.no_resep,
            dokter.nm_dokter,
            resep_obat.tgl_perawatan,
            resep_obat.jam,
            pasien.nm_pasien,
            reg_periksa.status_lanjut,
            round(sum(resep_dokter.jml * databarang.h_beli)) total
        ")
            ->join('resep_obat', 'resep_dokter.no_resep', '=', 'resep_obat.no_resep')
            ->join('reg_periksa', 'resep_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('databarang', 'resep_dokter.kode_brng', '=', 'databarang.kode_brng')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('dokter', 'resep_obat.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('reg_periksa.status_bayar', 'Sudah Bayar')
            ->whereBetween('resep_obat.tgl_perawatan', [$periodeAwal, $periodeAkhir])
            ->when(!empty($jenisPerawatan), fn (Builder $query) => $query->where('reg_periksa.status_lanjut', $jenisPerawatan))
            ->groupBy([
                'resep_dokter.no_resep',
                'dokter.nm_dokter',
                'resep_obat.tgl_perawatan',
                'resep_obat.jam',
                'pasien.nm_pasien',
                'reg_periksa.status_lanjut',
            ]);
    }
}
