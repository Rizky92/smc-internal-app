<?php

namespace App\Models\Farmasi;

use App\Models\Dokter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Resep extends Model
{
    protected $primaryKey = 'no_resep';

    protected $keyType = 'string';

    protected $table = 'resep_obat';

    public $incrementing = false;

    public $timestamps = false;

    public function scopePenggunaanObatPerDokter(Builder $query, string $dateMin = '', string $dateMax = ''): Builder
    {
        return $query
            ->selectRaw("
                resep_obat.no_resep,
                resep_obat.tgl_perawatan,
                resep_obat.jam,
                databarang.nama_brng,
                SUM(resep_dokter.jml) jumlah,
                dokter.nm_dokter,
                poliklinik.nm_poli
            ")
            ->join('reg_periksa', 'resep_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->leftJoin('dokter', 'resep_obat.kd_dokter', '=', 'dokter.kd_dokter')
            ->leftJoin('resep_dokter', 'resep_obat.no_resep', '=', 'resep_dokter.no_resep')
            ->leftJoin('databarang', 'resep_dokter.kode_brng', '=', 'databarang.kode_brng')
            ->where('resep_obat.tgl_perawatan', '>', '0000-00-00')
            ->where('resep_obat.jam', '>', '0000-00-00')
            ->when(!empty($dateMin) || !empty($dateMax), function (Builder $query) use ($dateMin, $dateMax) {
                return $query->whereBetween('resep_obat.tgl_perawatan', [$dateMin, $dateMax]);
            })
            ->where('reg_periksa.status_bayar', 'Sudah Bayar')
            ->where('reg_periksa.stts', '!=', 'Batal')
            ->whereNotNull('resep_dokter.kode_brng')
            ->groupBy(['resep_obat.no_resep', 'resep_obat.kd_dokter'])
            ->orderBy('resep_obat.no_resep');
    }

    public function dokterPeresep()
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

    public function obat()
    {
        return $this->belongsToMany(DataBarang::class, 'resep_dokter', 'no_resep', 'kode_brng')
            ->withPivot(ResepDokter::$pivotColumns)
            ->using(ResepDokter::class);
    }
}
