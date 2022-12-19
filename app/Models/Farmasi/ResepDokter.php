<?php

namespace App\Models\Farmasi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResepDokter extends Model
{
    protected $primaryKey = 'no_resep';

    protected $keyType = 'string';

    protected $table = 'resep_dokter';

    public $incrementing = false;

    public $timestamps = false;

    public function resepObat(): BelongsTo
    {
        return $this->belongsTo(ResepObat::class, 'no_resep', 'no_resep');
    }

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class, 'kode_brng', 'kode_brng');
    }

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
            ->when(!empty($jenisPerawatan), function (Builder $query) use ($jenisPerawatan) {
                return $query->where('reg_periksa.status_lanjut', $jenisPerawatan);
            })
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
