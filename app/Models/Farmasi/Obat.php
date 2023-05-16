<?php

namespace App\Models\Farmasi;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Obat extends Model
{
    use Searchable, Sortable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kode_brng';

    protected $keyType = 'string';

    protected $table = 'databarang';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * @TODO  change joins to relationshipS and aggregates
     */
    public function scopeDaruratStok(Builder $query): Builder
    {
        $sqlSelect = <<<SQL
            databarang.kode_brng,
            databarang.nama_brng,
            kodesatuan.satuan satuan_kecil,
            kategori_barang.nama kategori,
            databarang.stokminimal,
            ifnull(round(stok_gudang_ifi.stok_di_gudang, 2), 0) stok_sekarang_ifi,
            ifnull(round(stok_gudang_ap.stok_di_gudang, 2), 0) stok_sekarang_ap,
            round(databarang.stokminimal - ifnull(stok_gudang_ap.stok_di_gudang, 0), 2) saran_order,
            industrifarmasi.nama_industri,
            round(databarang.h_beli, 2) harga_beli,
            round((databarang.stokminimal - ifnull(stok_gudang_ap.stok_di_gudang, 0)) * databarang.h_beli, 2) harga_beli_total,
            ifnull((select ifnull(round(dp.h_pesan/databarang.isi, 2), 0) from detailpesan dp left join pemesanan p on p.no_faktur = dp.no_faktur where dp.kode_brng = databarang.kode_brng order by p.tgl_pesan desc limit 1), 0) harga_beli_terakhir,
            ifnull((select ifnull(dp.dis, 0) from detailpesan dp left join pemesanan p on p.no_faktur = dp.no_faktur where dp.kode_brng = databarang.kode_brng order by p.tgl_pesan desc limit 1), 0) diskon_terakhir,
            ifnull((select ds.nama_suplier from detailpesan dp left join pemesanan p on p.no_faktur = dp.no_faktur left join datasuplier ds on p.kode_suplier = ds.kode_suplier where dp.kode_brng = databarang.kode_brng order by p.tgl_pesan desc limit 1), '-') supplier_terakhir,
            (
                ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(now(), interval 2 week) and now()), 0) + 
                ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(now(), interval 2 week) and now()), 0)
            ) ke_pasien_14_hari,
            (
                ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(now(), interval 1 week) and now()), 0) + 
                ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(now(), interval 1 week) and now()), 0) +
                ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(now(), interval 1 week) and now()), 0)
            ) pemakaian_1_minggu,
            (
                ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(now(), interval 1 month) and now()), 0) + 
                ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(now(), interval 1 month) and now()), 0) +
                ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(now(), interval 1 month) and now()), 0)
            ) pemakaian_1_bulan,
            (
                ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(now(), interval 3 month) and now()), 0) + 
                ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(now(), interval 3 month) and now()), 0) +
                ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(now(), interval 3 month) and now()), 0)
            ) pemakaian_3_bulan
        SQL;

        $stokGudangAP = DB::raw("(
            select kode_brng, sum(stok) stok_di_gudang
            from gudangbarang
            inner join bangsal on gudangbarang.kd_bangsal = bangsal.kd_bangsal
            where bangsal.status = '1'
            and gudangbarang.kd_bangsal = 'AP'
            group by kode_brng
        ) stok_gudang_ap");

        $stokGudangIFI = DB::raw("(
            select kode_brng, sum(stok) stok_di_gudang
            from gudangbarang
            inner join bangsal on gudangbarang.kd_bangsal = bangsal.kd_bangsal
            where bangsal.status = '1'
            and gudangbarang.kd_bangsal = 'IFI'
            group by kode_brng
        ) stok_gudang_ifi");

        return $query
            ->selectRaw($sqlSelect)
            ->join('kategori_barang', 'databarang.kode_kategori', '=', 'kategori_barang.kode')
            ->join('kodesatuan', 'databarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->join('industrifarmasi', 'databarang.kode_industri', '=', 'industrifarmasi.kode_industri')
            ->leftJoin($stokGudangAP, 'databarang.kode_brng', '=', 'stok_gudang_ap.kode_brng')
            ->leftJoin($stokGudangIFI, 'databarang.kode_brng', '=', 'stok_gudang_ifi.kode_brng')
            ->where('databarang.status', '1')
            ->where('databarang.stokminimal', '>', 0)
            ->whereRaw('(databarang.stokminimal - ifnull(stok_gudang_ap.stok_di_gudang, 0)) > ?', [0])
            ->whereRaw('ifnull(stok_gudang_ap.stok_di_gudang, 0) <= databarang.stokminimal');
    }
}
