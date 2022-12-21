<?php

namespace App\Models\Farmasi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ResepObatRacikanDetail extends Model
{
    protected $primaryKey = null;

    protected $table = 'detail_obat_racikan';

    public $incrementing = false;

    public $timestamps = false;

    public function scopePendapatanObatRacikan(Builder $query, string $poli = ''): Builder
    {
        return $query->selectRaw("
            CEIL(SUM(databarang.h_beli)) jumlah,
	        DATE_FORMAT(detail_obat_racikan.tgl_perawatan, '%m-%Y') bulan
        ")
            ->join('databarang', 'detail_obat_racikan.kode_brng', '=', 'databarang.kode_brng')
            ->join('reg_periksa', 'detail_obat_racikan.no_rawat', '=', 'reg_periksa.no_rawat')
            ->whereNotIn('reg_periksa.stts', ['Belum', 'Batal'])
            ->where('reg_periksa.status_bayar', 'Sudah Bayar')
            ->where('databarang.kode_kategori', 'NOT LIKE', "3.%")
            ->when(!empty($poli), function (Builder $query) use ($poli) {
                switch (Str::lower($poli)) {
                    case 'ralan':
                        return $query->where('reg_periksa.status_lanjut', 'Ralan')
                            ->where('reg_periksa.kd_poli', '!=', 'IGDK');
                    case 'ranap':
                        return $query->where('reg_periksa.status_lanjut', 'Ranap')
                            ->where('reg_periksa.kd_poli', '!=', 'IGDK');
                    case 'igd':
                        return $query->where('reg_periksa.kd_poli', 'IGDK');
                }
            })
            ->whereBetween('detail_obat_racikan.tgl_perawatan', [
                now()->startOfYear()->format('Y-m-d'),
                now()->endOfYear()->format('Y-m-d')
            ])
            ->groupByRaw("DATE_FORMAT(detail_obat_racikan.tgl_perawatan, '%m-%Y')");
    }

    public static function pendapatanRacikanObatRalan(): array
    {
        return (new static)->pendapatanObatRacikan('Ralan')->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }

    public static function pendapatanRacikanObatRanap(): array
    {
        return (new static)->pendapatanObatRacikan('Ranap')->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }

    public static function pendapatanRacikanObatIGD(): array
    {
        return (new static)->pendapatanObatRacikan('Igd')->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }
}
