<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Resep extends Model
{
    protected $primaryKey = 'no_resep';

    protected $keyType = 'string';

    protected $table = 'resep_obat';

    public $incrementing = false;

    public $timestamps = false;

    public function scopePenggunaanObatPerDokter(Builder $query, $dateMin, $dateMax)
    {
        return $query
            ->selectRaw("
                resep_obat.no_resep,
                resep_obat.tgl_peresepan,
                dokter.nm_dokter,
                resep_dokter.jml,
                databarang.nama_brng
            ")
            ->join('dokter', 'resep_obat.kd_dokter', '=', 'dokter.kd_dokter')
            ->join('resep_dokter', 'resep_obat.no_resep', '=', 'resep_dokter.no_resep')
            ->join('databarang', 'resep_dokter.kode_brng', '=', 'databarang.kode_brng')
            ->whereBetween('resep_obat.tgl_peresepan', [$dateMin, $dateMax])
            ->where(function (Builder $query) {
                return $query
                    ->whereNotNull(['resep_obat.jam', 'resep_obat.tgl_peresepan', 'resep_obat.jam_peresepan'])
                    ->where('resep_obat.kd_dokter', '!=', '-');
            });
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
