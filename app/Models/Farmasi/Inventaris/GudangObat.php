<?php

namespace App\Models\Farmasi\Inventaris;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Str;

class GudangObat extends Model
{
    protected $primaryKey = null;

    protected $keyType = null;

    protected $table = 'gudangbarang';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeStokPerRuangan(Builder $query, string $periodeAwal = '', string $periodeAkhir = '', string $cari = ''): Builder
    {
        // if (empty($periodeAwal)) {
        //     $periodeAwal = now()->startOfMonth()->format('Y-m-d');
        // }

        // if (empty($periodeAkhir)) {
        //     $periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        // }

        return $query->selectRaw("
            bangsal.nm_bangsal,
            gudangbarang.kode_brng,
            databarang.nama_brng,
            gudangbarang.stok,
            kodesatuan.satuan,
        ")
            ->leftJoin('databarang', 'gudangbarang.kode_brng', '=', 'databarang.kode_brng')
            ->leftJoin('kodesatuan', 'databarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->leftJoin('bangsal', 'gudangbarang.kd_bangsal', '=', 'bangsal.kd_bangsal')
            ->when(! empty($cari), function (Builder $query) use ($cari) {
                return $query->where(function (Builder $query) use ($cari) {
                    $cari = Str::lower($cari);

                    return $query->where('gudangbarang.kd_bangsal', 'like', "%{$cari}%")
                        ->orWhere('bangsal.nm_bangsal', 'like', "%{$cari}%")
                        ->orWhere('databarang.kode_brng', 'like', "%{$cari}%")
                        ->orWhere('databarang.nama_brng', 'like', "%{$cari}%");
                });
            });
            // ->leftJoin('opname', function (JoinClause $join) {
            //     return $join
            //         ->on('gudangbarang.kode_brng', '=', 'opname.kode_brng')
            //         ->on('gudangbarang.kd_bangsal', '=', 'opname.kd_bangsal');
            // })
            // ->whereBetween('opname.tanggal', [$periodeAwal, $periodeAkhir])
            // ->when(! empty($cari), function (Builder $query) use ($cari) {
            //     return $query->where(function (Builder $query) use ($cari) {
            //         $cari = Str::lower($cari);

            //         return $query->where('gudangbarang.kd_bangsal', 'like', "%{$cari}%")
            //             ->orWhere('bangsal.nm_bangsal', 'like', "%{$cari}%")
            //             ->orWhere('databarang.kode_brng', 'like', "%{$cari}%")
            //             ->orWhere('databarang.nama_brng', 'like', "%{$cari}%")
            //             ->orWhere('opname.keterangan', 'like', "%{$cari}%");
            //     });
            // });

        // select
        //     bangsal.nm_bangsal,
        //     gudangbarang.kode_brng,
        //     databarang.nama_brng,
        //     gudangbarang.stok,
        //     kodesatuan.satuan,
        //     CURRENT_DATE() stok_gudang_per_tanggal,
        //     opname.tanggal tanggal_opname_terakhir,
        //     opname.stok stok_opname,
        //     opname.`real` real_opname,
        //     opname.selisih selisih_stok_opname,
        //     opname.keterangan
        // from gudangbarang
        // left join databarang on gudangbarang.kode_brng = databarang.kode_brng
        // left join kodesatuan on databarang.kode_sat = kodesatuan.kode_sat
        // left join bangsal on gudangbarang.kd_bangsal = bangsal.kd_bangsal
        // left join opname on gudangbarang.kode_brng = opname.kode_brng and gudangbarang.kd_bangsal = opname.kd_bangsal
        // where gudangbarang.kd_bangsal = 'IFG'
    }
}
