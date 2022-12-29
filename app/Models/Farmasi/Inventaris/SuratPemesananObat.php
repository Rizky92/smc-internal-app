<?php

namespace App\Models\Farmasi\Inventaris;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class SuratPemesananObat extends Model
{
    protected $primaryKey = 'no_pemesanan';

    protected $keyType = 'string';

    protected $table = 'surat_pemesanan_medis';

    public $incrementing = false;

    public $timestamps = false;

    public function scopePerbandinganPemesananObatPO(
        Builder $query,
        string $periodeAwal = '',
        string $periodeAkhir = '',
        string $cari = '',
        bool $hanyaTampilkanYangBerbeda = false
    ): Builder
    {
        if (empty($periodeAwal)) {
            $periodeAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($periodeAkhir)) {
            $periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $pemesananYangDatang = DB::raw("(
            select
                pemesanan.no_order,
                pemesanan.tgl_pesan,
                detailpesan.kode_brng,
                detailpesan.jumlah2 as jumlah,
                pemesanan.kode_suplier,
                datasuplier.nama_suplier
            from pemesanan
            inner join datasuplier on pemesanan.kode_suplier = datasuplier.kode_suplier
            inner join detailpesan on pemesanan.no_faktur = detailpesan.no_faktur
            inner join databarang on detailpesan.kode_brng = databarang.kode_brng
        ) pemesanan_datang");

        return $query->selectRaw("
            surat_pemesanan_medis.no_pemesanan,
            databarang.nama_brng,
            datasuplier.nama_suplier suplier_pesan,
            ifnull(pemesanan_datang.nama_suplier, '-') suplier_datang,
            detail_surat_pemesanan_medis.jumlah2 jumlah_pesan,
            ifnull(pemesanan_datang.jumlah, 0) jumlah_datang,
            ifnull((detail_surat_pemesanan_medis.jumlah2 - pemesanan_datang.jumlah), 'Barang tidak datang') selisih
        ")
            ->join('datasuplier', 'surat_pemesanan_medis.kode_suplier', '=', 'datasuplier.kode_suplier')
            ->join('detail_surat_pemesanan_medis', 'surat_pemesanan_medis.no_pemesanan', '=', 'detail_surat_pemesanan_medis.no_pemesanan')
            ->join('databarang', 'detail_surat_pemesanan_medis.kode_brng', '=', 'databarang.kode_brng')
            ->leftJoin($pemesananYangDatang, function (JoinClause $join) {
                return $join
                    ->on('surat_pemesanan_medis.no_pemesanan', '=', 'pemesanan_datang.no_order')
                    ->on('detail_surat_pemesanan_medis.kode_brng', '=', 'pemesanan_datang.kode_brng');
            })
            ->whereBetween('pemesanan_datang.tgl_pesan', [$periodeAwal, $periodeAkhir])
            ->when($hanyaTampilkanYangBerbeda, function (Builder $query) {
                return $query->where(DB::raw('(detail_surat_pemesanan_medis.jumlah2 - pemesanan_datang.jumlah)'), '!=', 0);
            })
            ->when(!empty($cari), function (Builder $query) use ($cari) {
                return $query->where(function (Builder $query) use ($cari) {
                    return $query
                        ->where('surat_pemesanan_medis.no_pemesanan', 'like', "%{$cari}%")
                        ->orWhere('detail_surat_pemesanan_medis.kode_brng', 'like', "%{$cari}%")
                        ->orWhere('databarang.nama_brng', 'like', "%{$cari}%")
                        ->orWhere('datasuplier.nama_suplier', 'like', "%{$cari}%")
                        ->orWhere('pemesanan_datang.nama_suplier', 'like', "%{$cari}%");
                });
            });
    }
}
