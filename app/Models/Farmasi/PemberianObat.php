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

    public function scopePendapatanObat(Builder $query, string $jenisPerawatan = '', bool $selainFarmasi = false): Builder
    {
        return $query->selectRaw("
            round(sum(detail_pemberian_obat.total)) jumlah,
            date_format(detail_pemberian_obat.tgl_perawatan, '%m-%Y') bulan
        ")
            ->leftJoin('reg_periksa', 'detail_pemberian_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('databarang', 'detail_pemberian_obat.kode_brng', '=', 'databarang.kode_brng')
            ->whereBetween('detail_pemberian_obat.tgl_perawatan', [now()->startOfYear()->format('Y-m-d'), now()->endOfYear()->format('Y-m-d')])
            ->when(!empty($jenisPerawatan), function (Builder $query) use ($jenisPerawatan) {
                switch (Str::lower($jenisPerawatan)) {
                    case 'ralan':
                        return $query->where('reg_periksa.status_lanjut', 'Ralan')
                            ->where('reg_periksa.kd_poli', '!=', 'IGDK');
                    case 'ranap':
                        return $query->where('reg_periksa.status_lanjut', 'Ranap')
                            ->where('reg_periksa.kd_poli', '!=', 'IGDK');
                    case 'igd':
                        return $query->where('reg_periksa.kd_poli', '=', 'IGDK');
                }
            })
            ->when($selainFarmasi, function (Builder $query) {
                return $query->where('databarang.kode_kategori', 'like', '3.%');
            })
            ->groupByRaw("date_format(detail_pemberian_obat.tgl_perawatan, '%m-%Y')");
    }

    public static function pendapatanObatRalan()
    {
        return (new static)::pendapatanObat('ralan')->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }

    public static function pendapatanObatRanap()
    {
        return (new static)::pendapatanObat('ranap')->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }

    public static function pendapatanObatIGD()
    {
        return (new static)::pendapatanObat('igd')->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }

    public static function pendapatanAlkesUnit()
    {
        return (new static)::pendapatanObat('', true)->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }
}
