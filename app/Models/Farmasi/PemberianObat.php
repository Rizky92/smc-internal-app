<?php

namespace App\Models\Farmasi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PemberianObat extends Model
{
    protected $primaryKey = false;

    protected $keyType = null;

    protected $table = 'detail_pemberian_obat';

    public $incrementing = false;

    public $timestamps = false;

    public function scopePendapatanObat(Builder $query, string $jenisPerawatan = '', string $year = '2022', bool $selainFarmasi = false): Builder
    {
        return $query->selectRaw("
            round(sum(detail_pemberian_obat.total)) jumlah,
            month(detail_pemberian_obat.tgl_perawatan) bulan
        ")
            ->leftJoin('reg_periksa', 'detail_pemberian_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('databarang', 'detail_pemberian_obat.kode_brng', '=', 'databarang.kode_brng')
            ->whereBetween('detail_pemberian_obat.tgl_perawatan', ["{$year}-01-01", "{$year}-12-31"])
            ->when(!empty($jenisPerawatan), function (Builder $query) use ($jenisPerawatan) {
                switch (Str::lower($jenisPerawatan)) {
                    case 'ralan':
                        return $query->where('detail_pemberian_obat.status', 'Ralan')
                            ->where('reg_periksa.kd_poli', '!=', 'IGDK');
                    case 'ranap':
                        return $query->where('detail_pemberian_obat.status', 'Ranap');
                    case 'igd':
                        return $query->where('detail_pemberian_obat.status', 'Ralan')
                            ->where('reg_periksa.kd_poli', '=', 'IGDK');
                }
            })
            ->when($selainFarmasi, function (Builder $query) {
                return $query->where('databarang.kode_kategori', 'like', '3.%');
            })
            ->groupByRaw('month(detail_pemberian_obat.tgl_perawatan)');
    }

    public static function pendapatanObatRalan(string $year = '2022'): array
    {
        $data = (new static)::pendapatanObat('ralan', $year)->get()
            ->mapWithKeys(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->toArray();

        return map_bulan($data);
    }

    public static function pendapatanObatRanap(string $year = '2022'): array
    {
        $data = (new static)::pendapatanObat('ranap', $year)->get()
            ->mapWithKeys(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->toArray();

        return map_bulan($data);
    }

    public static function pendapatanObatIGD(string $year = '2022'): array
    {
        $data = (new static)::pendapatanObat('IGD', $year)->get()
            ->mapWithKeys(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->toArray();

        return map_bulan($data);
    }

    public static function pendapatanAlkesUnit(string $year = '2022'): array
    {
        $data = (new static)::pendapatanObat('', $year, true)->get()
            ->mapWithKeys(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->toArray();

        return map_bulan($data);
    }
}
