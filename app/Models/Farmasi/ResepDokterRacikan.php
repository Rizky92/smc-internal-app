<?php

namespace App\Models\Farmasi;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ResepDokterRacikan extends Model
{
    use Searchable, Sortable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_resep';

    protected $keyType = 'string';

    protected $table = 'resep_dokter_racikan';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeKunjunganResepObatRacikan(
        Builder $query,
        string $tglAwal = '',
        string $tglAkhir = '',
        string $jenisPerawatan = ''
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        return $query->selectRaw("
            resep_dokter_racikan.no_resep,
            dokter.nm_dokter,
            resep_obat.tgl_perawatan,
            resep_obat.jam,
            pasien.nm_pasien,
            poliklinik.nm_poli,
            reg_periksa.status_lanjut,
            round(sum(resep_dokter_racikan_detail.jml * databarang.h_beli)) total
        ")
            ->join('resep_obat', 'resep_dokter_racikan.no_resep', '=', 'resep_obat.no_resep')
            ->join('reg_periksa', 'resep_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('resep_dokter_racikan_detail', 'resep_dokter_racikan.no_resep', '=', 'resep_dokter_racikan_detail.no_resep')
            ->join('databarang', 'resep_dokter_racikan_detail.kode_brng', '=', 'databarang.kode_brng')
            ->join('dokter', 'resep_obat.kd_dokter', '=', 'dokter.kd_dokter')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->where('reg_periksa.status_bayar', 'Sudah Bayar')
            ->whereBetween('resep_obat.tgl_perawatan', [$tglAwal, $tglAkhir])
            ->when(!empty($jenisPerawatan), fn (Builder $query) => $query->where('reg_periksa.status_lanjut', $jenisPerawatan))
            ->groupBy([
                'resep_dokter_racikan.no_resep',
                'dokter.nm_dokter',
                'resep_obat.tgl_perawatan',
                'resep_obat.jam',
                'pasien.nm_pasien',
                'reg_periksa.status_lanjut',
            ]);
    }
}
