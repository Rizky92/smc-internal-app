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

    public function scopePerbandinganObatPO(
        Builder $query,
        string $periodeAwal = '',
        string $periodeAkhir = '',
        string $berdasarkan = 'tanggal pesan',
        string $statusPemesanan = 'sudah datang',
        string $statusPenerimaan = 'sudah dibayar'
    ): Builder {
        if (empty($periodeAwal)) {
            $periodeAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($periodeAkhir)) {
            $periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $tgl = [$periodeAwal, $periodeAkhir];

        $barangDipesan = DB::raw("(
                select
                    detail_surat_pemesanan_medis.kode_brng,
                    datasuplier.nama_suplier,
                    surat_pemesanan_medis.tanggal tgl_pesan,
                    sum(detail_surat_pemesanan_medis.jumlah2) jumlah
                from detail_surat_pemesanan_medis
                left join surat_pemesanan_medis on detail_surat_pemesanan_medis.no_pemesanan  = surat_pemesanan_medis.no_pemesanan
                left join datasuplier on surat_pemesanan_medis.kode_suplier = datasuplier.kode_suplier
                where surat_pemesanan_medis.status = '{$statusPemesanan}'
                group by detail_surat_pemesanan_medis.kode_brng,
                    surat_pemesanan_medis.kode_suplier
            ) barang_dipesan");

        $barangDiterima = DB::raw("(
                select
                    detailpesan.kode_brng,
                    datasuplier.nama_suplier,
                    pemesanan.tgl_pesan tgl_datang,
                    sum(detailpesan.jumlah2) jumlah
                from detailpesan
                left join pemesanan on detailpesan.no_faktur = pemesanan.no_faktur
                left join datasuplier on pemesanan.kode_suplier = datasuplier.kode_suplier
                where pemesanan.status = '{$statusPenerimaan}'
                group by detailpesan.kode_brng,
                    pemesanan.kode_suplier
            ) barang_diterima");

        return $query->selectRaw("
            databarang.kode_brng,
            databarang.nama_brng,
            kodesatuan.satuan,
            barang_dipesan.nama_suplier suplier_pesan,
            barang_diterima.nama_suplier suplier_diterima,
            barang_dipesan.jumlah jumlah_dipesan,
            barang_diterima.jumlah jumlah_datang,
            (barang_dipesan.jumlah - barang_diterima.jumlah) selisih
        ")
            ->leftJoin('kodesatuan', 'databarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->leftJoin($barangDipesan, 'databarang.kode_brng', '=', 'barang_dipesan.kode_brng')
            ->leftJoin($barangDiterima, 'databarang.kode_brng', '=', 'barang_diterima.kode_brng')
            ->where(function (Builder $query) use ($berdasarkan, $tgl) {
                switch ($berdasarkan) {
                    case 'tanggal pesan':
                        return $query->whereBetween('barang_dipesan.tgl_pesan', $tgl);
                    case 'tanggal datang':
                        return $query->whereBetween('barang_diterima.tgl_datang', $tgl);
                }
            })
            ->orderBy('databarang.kode_brng');
    }
}
