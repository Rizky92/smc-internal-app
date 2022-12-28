<?php

namespace App\Models\Farmasi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Obat extends Model
{
    protected $primaryKey = 'kode_brng';

    protected $keyType = 'string';

    protected $table = 'databarang';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeJanganTampilkanStokMinimalNol(Builder $query)
    {
        return $query->where('stokminimal', '>', 0);
    }

    public function scopeDaruratStok(Builder $query, string $cari = ''): Builder
    {
        return $query
            ->selectRaw("
                databarang.kode_brng,
                nama_brng,
                kodesatuan.satuan satuan_kecil,
                kategori_barang.nama kategori,
                stokminimal,
                IFNULL(ROUND(stok_gudang.stok_di_gudang, 2), 0) stok_sekarang,
                (databarang.stokminimal - IFNULL(stok_gudang.stok_di_gudang, 0)) saran_order,
                industrifarmasi.nama_industri,
                CEIL(databarang.h_beli) harga_beli,
                CEIL((databarang.stokminimal - IFNULL(stok_gudang.stok_di_gudang, 0)) * databarang.h_beli) harga_beli_total
            ")
            ->join('kategori_barang', 'databarang.kode_kategori', '=', 'kategori_barang.kode')
            ->join('kodesatuan', 'databarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->join('industrifarmasi', 'databarang.kode_industri', '=', 'industrifarmasi.kode_industri')
            ->leftJoin(DB::raw("(
                SELECT
                    kode_brng,
                    SUM(stok) stok_di_gudang
                FROM gudangbarang
                INNER JOIN bangsal ON gudangbarang.kd_bangsal = bangsal.kd_bangsal
                WHERE bangsal.status = '1'
                AND gudangbarang.kd_bangsal = 'AP'
                GROUP BY kode_brng
            ) stok_gudang"), 'databarang.kode_brng', '=', 'stok_gudang.kode_brng')
            ->where('status', '1')
            ->where('stokminimal', '>', '0')
            ->whereRaw('(databarang.stokminimal - IFNULL(stok_gudang.stok_di_gudang, 0)) > 0')
            ->whereRaw('IFNULL(stok_gudang.stok_di_gudang, 0) <= stokminimal')
            ->when(!empty($cari), function (Builder $query) use ($cari) {
                return $query->where(function (Builder $query) use ($cari) {
                    return $query->where('databarang.kode_brng', 'LIKE', "%{$cari}%")
                        ->orWhere('databarang.nama_brng', 'LIKE', "%{$cari}%")
                        ->orWhere('kategori_barang.nama', 'LIKE', "%{$cari}%")
                        ->orWhere('industrifarmasi.nama_industri', 'LIKE', "%{$cari}%");
                });
            });
    }

    public function scopePerbandinganObatPO(Builder $query, string $periodeAwal = '', string $periodeAkhir = ''): Builder
    {
        if (empty($periodeAwal)) {
            $periodeAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($periodeAkhir)) {
            $periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        return $query->selectRaw("
            surat_pemesanan_medis.no_pemesanan,
            databarang.kode_brng,
            databarang.nama_brng,
            kodesatuan.satuan,
            suplierpesan.nama_suplier nama_suplier_dipesan,
            suplierdatang.nama_suplier nama_suplier_datang,
            sum(detail_surat_pemesanan_medis.jumlah2) jumlah_dipesan,
            sum(detailpesan.jumlah2) jumlah_datang,
            (sum(detail_surat_pemesanan_medis.jumlah2) - sum(detailpesan.jumlah2)) selisih
        ")
            ->leftJoin('kodesatuan', 'databarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->leftJoin('detail_surat_pemesanan_medis', 'databarang.kode_brng', '=', 'detail_surat_pemesanan_medis.kode_brng')
            ->leftJoin('surat_pemesanan_medis', 'detail_surat_pemesanan_medis.no_pemesanan', '=', 'surat_pemesanan_medis.no_pemesanan')
            ->leftJoin('pemesanan', 'surat_pemesanan_medis.no_pemesanan', '=', 'pemesanan.no_order')
            ->leftJoin('detailpesan', 'pemesanan.no_faktur', '=', 'detailpesan.no_faktur')
            ->leftJoin(DB::raw('datasuplier suplierpesan'), 'surat_pemesanan_medis.kode_suplier', '=', 'suplierpesan.kode_suplier')
            ->leftJoin(DB::raw('datasuplier suplierdatang'), 'pemesanan.kode_suplier', '=', 'suplierdatang.kode_suplier')
            ->whereBetween('pemesanan.tgl_pesan', [$periodeAwal, $periodeAkhir])
            ->where('surat_pemesanan_medis.status', 'sudah datang')
            ->groupBy(['databarang.kode_brng', 'surat_pemesanan_medis.no_pemesanan'])
            ->orderBy('databarang.kode_brng');
    }
}
