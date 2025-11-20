<?php

namespace App\Models\Logistik;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BarangNonMedis extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kode_brng';

    protected $keyType = 'string';

    protected $table = 'ipsrsbarang';

    public $incrementing = false;

    public $timestamps = false;

    protected $searchColumns = [
        'kode_brng',
        'nama_brng',
        'kode_sat',
        'jenis',
    ];

    public function scopeDenganMinmax(Builder $query): Builder
    {
        $db = DB::connection('mysql_smc')->getDatabaseName();

        $sqlSelect = <<<SQL
            ipsrsbarang.kode_brng,
            ipsrsbarang.nama_brng,
            ifnull(ipsrssuplier.kode_suplier, '-') kode_supplier,
            ifnull(ipsrssuplier.nama_suplier, '-') nama_supplier,
            ipsrsjenisbarang.nm_jenis jenis,
            kodesatuan.satuan,
            ifnull({$db}.ipsrs_minmax_stok_barang.stok_min, 0) stokmin,
            ifnull({$db}.ipsrs_minmax_stok_barang.stok_max, 0) stokmax,
            ipsrsbarang.stok,
            if(
                ipsrsbarang.stok <= ifnull({$db}.ipsrs_minmax_stok_barang.stok_min, 0),
                ifnull(ifnull({$db}.ipsrs_minmax_stok_barang.stok_max, ifnull({$db}.ipsrs_minmax_stok_barang.stok_min, 0)) - ipsrsbarang.stok, 0),
                0
            ) saran_order,
            ipsrsbarang.harga,
            if(
                ipsrsbarang.stok <= ifnull({$db}.ipsrs_minmax_stok_barang.stok_min, 0),
                ipsrsbarang.harga * (ifnull({$db}.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok),
                0
            ) total_harga
            SQL;

        $this->addSearchConditions([
            'ipsrsbarang.kode_brng',
            'ipsrsbarang.nama_brng',
            "IFNULL(ipsrssuplier.kode_suplier, '-')",
            "IFNULL(ipsrssuplier.nama_suplier, '-')",
            'ipsrsjenisbarang.nm_jenis',
            'kodesatuan.satuan',
        ]);

        $this->addRawColumns([
            'kode_supplier' => DB::raw("IFNULL(ipsrssuplier.kode_suplier, '-')"),
            'nama_supplier' => DB::raw("IFNULL(ipsrssuplier.nama_suplier, '-')"),
            'jenis'         => 'ipsrsjenisbarang.nm_jenis',
            'stokmin'       => DB::raw("IFNULL({$db}.ipsrs_minmax_stok_barang.stok_min, 0)"),
            'stokmax'       => DB::raw("IFNULL({$db}.ipsrs_minmax_stok_barang.stok_max, 0)"),
            'saran_order'   => DB::raw("IF(ipsrsbarang.stok <= IFNULL({$db}.ipsrs_minmax_stok_barang.stok_min, 0), IFNULL(IFNULL({$db}.ipsrs_minmax_stok_barang.stok_max, IFNULL({$db}.ipsrs_minmax_stok_barang.stok_min, 0)) - ipsrsbarang.stok, 0), 0)"),
            'total_harga'   => DB::raw("IF(ipsrsbarang.stok <= IFNULL({$db}.ipsrs_minmax_stok_barang.stok_min, 0), ipsrsbarang.harga * (IFNULL({$db}.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok), 0)"),
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('ipsrsjenisbarang', 'ipsrsbarang.jenis', '=', 'ipsrsjenisbarang.kd_jenis')
            ->leftJoin('kodesatuan', 'ipsrsbarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->leftJoin("{$db}.ipsrs_minmax_stok_barang", 'ipsrsbarang.kode_brng', '=', "{$db}.ipsrs_minmax_stok_barang.kode_brng")
            ->leftJoin('ipsrssuplier', "{$db}.ipsrs_minmax_stok_barang.kode_suplier", '=', 'ipsrssuplier.kode_suplier')
            ->where('ipsrsbarang.status', '1');
    }

    public function scopeDaruratStok(Builder $query, bool $saranOrderNol = true): Builder
    {
        $db = DB::connection('mysql_smc')->getDatabaseName();

        $sqlSelect = <<<SQL
            ipsrsbarang.kode_brng,
            ipsrsbarang.nama_brng,
            ifnull(ipsrssuplier.nama_suplier, '-') nama_supplier,
            ipsrsjenisbarang.nm_jenis jenis,
            kodesatuan.satuan,
            ifnull({$db}.ipsrs_minmax_stok_barang.stok_min, 0) stokmin,
            ifnull({$db}.ipsrs_minmax_stok_barang.stok_max, 0) stokmax,
            ipsrsbarang.stok,
            ifnull(ifnull({$db}.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok, '0') saran_order,
            ipsrsbarang.harga,
            (ipsrsbarang.harga * (ifnull({$db}.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok)) total_harga
        SQL;

        $this->addSearchConditions([
            'ipsrsbarang.kode_brng',
            'ipsrsbarang.nama_brng',
            "IFNULL(ipsrssuplier.nama_suplier, '-')",
            'ipsrsjenisbarang.nm_jenis',
            'kodesatuan.satuan',
        ]);

        $this->addRawColumns([
            'nama_supplier' => "IFNULL(ipsrssuplier.nama_suplier, '-')",
            'jenis'         => 'ipsrsjenisbarang.nm_jenis',
            'stokmin'       => DB::raw('IFNULL(smc.ipsrs_minmax_stok_barang.stok_min, 0)'),
            'stokmax'       => DB::raw('IFNULL(smc.ipsrs_minmax_stok_barang.stok_max, 0)'),
            'saran_order'   => DB::raw("IFNULL(IFNULL(smc.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok, '0')"),
            'total_harga'   => DB::raw('(ipsrsbarang.harga * (IFNULL(smc.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok))'),
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('ipsrsjenisbarang', 'ipsrsbarang.jenis', '=', 'ipsrsjenisbarang.kd_jenis')
            ->leftJoin('kodesatuan', 'ipsrsbarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->leftJoin("{$db}.ipsrs_minmax_stok_barang", 'ipsrsbarang.kode_brng', '=', "{$db}.ipsrs_minmax_stok_barang.kode_brng")
            ->leftJoin('ipsrssuplier', "{$db}.ipsrs_minmax_stok_barang.kode_suplier", '=', 'ipsrssuplier.kode_suplier')
            ->where('ipsrsbarang.status', '1')
            ->where('ipsrsbarang.stok', '<=', DB::raw("ifnull({$db}.ipsrs_minmax_stok_barang.stok_min, 0)"))
            ->when(! $saranOrderNol, fn (Builder $query) => $query->whereRaw("ifnull(ifnull({$db}.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok, '0') > 0"));
    }

    public function scopeSirkulasiNonMedis(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $sqlSelect = <<<SQL
            ipsrsbarang.kode_brng,
            ipsrsbarang.nama_brng,
            kodesatuan.kode_sat,
            ifnull((select dp.harga from ipsrsdetailpesan dp join ipsrspemesanan p on dp.no_faktur=p.no_faktur where dp.kode_brng=ipsrsbarang.kode_brng and p.tgl_pesan between ? and ? order by p.tgl_pesan asc limit 1), (select dp2.harga from ipsrsdetailpesan dp2 join ipsrspemesanan p2 on dp2.no_faktur=p2.no_faktur where dp2.kode_brng=ipsrsbarang.kode_brng and p2.tgl_pesan < ? order by p2.tgl_pesan desc limit 1)) harga,
            ifnull((select rb.stok_awal from ipsrs_riwayat_barang rb where rb.kode_brng = ipsrsbarang.kode_brng and rb.tanggal between ? and ? order by rb.tanggal asc, rb.jam asc limit 1), (select rb2.stok_akhir from ipsrs_riwayat_barang rb2 where rb2.kode_brng = ipsrsbarang.kode_brng and rb2.tanggal < ? order by rb2.tanggal desc, rb2.jam desc limit 1)) stok_awal,
            ifnull((select ipsrsdetailbeli.jumlah from ipsrsdetailbeli join ipsrspembelian on ipsrsdetailbeli.no_faktur = ipsrspembelian.no_faktur where ipsrsdetailbeli.kode_brng = ipsrsbarang.kode_brng and ipsrspembelian.tgl_beli between ? and ?), 0) pengadaan,
            ifnull((select sum(ipsrsdetailbeli.subtotal) from ipsrsdetailbeli join ipsrspembelian on ipsrsdetailbeli.no_faktur = ipsrspembelian.no_faktur where ipsrsdetailbeli.kode_brng = ipsrsbarang.kode_brng and ipsrspembelian.tgl_beli between ? and ?), 0) sub_total_pengadaan,
            ifnull((select sum(ipsrsdetailpesan.jumlah) from ipsrsdetailpesan join ipsrspemesanan on ipsrsdetailpesan.no_faktur = ipsrspemesanan.no_faktur where ipsrsdetailpesan.kode_brng = ipsrsbarang.kode_brng and ipsrspemesanan.tgl_pesan between ? and ?), 0) penerimaan,
            ifnull((select sum(ipsrsdetailpesan.subtotal) from ipsrsdetailpesan join ipsrspemesanan on ipsrsdetailpesan.no_faktur = ipsrspemesanan.no_faktur where ipsrsdetailpesan.kode_brng = ipsrsbarang.kode_brng and ipsrspemesanan.tgl_pesan between ? and ?), 0) sub_total_penerimaan,
            ifnull((select sum(ipsrsdetailpengeluaran.jumlah) from ipsrsdetailpengeluaran join ipsrspengeluaran on ipsrsdetailpengeluaran.no_keluar = ipsrspengeluaran.no_keluar where ipsrsdetailpengeluaran.kode_brng = ipsrsbarang.kode_brng and ipsrspengeluaran.tanggal between ? and ?), 0) stok_keluar,
            ifnull((select sum(ipsrsdetailpengeluaran.total) from ipsrsdetailpengeluaran join ipsrspengeluaran on ipsrsdetailpengeluaran.no_keluar = ipsrspengeluaran.no_keluar where ipsrsdetailpengeluaran.kode_brng = ipsrsbarang.kode_brng and ipsrspengeluaran.tanggal between ? and ?), 0) sub_total_keluar,
            ifnull((select sum(utd_pengambilan_penunjang.jml) from utd_pengambilan_penunjang where utd_pengambilan_penunjang.kode_brng = ipsrsbarang.kode_brng and utd_pengambilan_penunjang.tanggal between ? and ?), 0) pengambilan_utd,
            ifnull((select sum(utd_pengambilan_penunjang.total) from utd_pengambilan_penunjang where utd_pengambilan_penunjang.kode_brng = ipsrsbarang.kode_brng and utd_pengambilan_penunjang.tanggal between ? and ?), 0) sub_total_pengambilan_utd,
            ifnull((select sum(ipsrs_detail_hibah.jumlah) from ipsrs_detail_hibah join ipsrs_hibah on ipsrs_detail_hibah.no_hibah = ipsrs_hibah.no_hibah where ipsrs_detail_hibah.kode_brng = ipsrsbarang.kode_brng and ipsrs_hibah.tgl_hibah between ? and ?), 0) hibah,
            ifnull((select sum(ipsrs_detail_hibah.subtotalhibah) from ipsrs_detail_hibah join ipsrs_hibah on ipsrs_detail_hibah.no_hibah = ipsrs_hibah.no_hibah where ipsrs_detail_hibah.kode_brng = ipsrsbarang.kode_brng and ipsrs_hibah.tgl_hibah between ? and ?), 0) sub_total_hibah
        SQL;

        return $query
            ->selectRaw($sqlSelect, [
                $tglAwal, $tglAkhir, $tglAwal,
                $tglAwal, $tglAkhir, $tglAwal,
                $tglAwal, $tglAkhir,
                $tglAwal, $tglAkhir,
                $tglAwal, $tglAkhir,
                $tglAwal, $tglAkhir,
                $tglAwal, $tglAkhir,
                $tglAwal, $tglAkhir,
                $tglAwal, $tglAkhir,
                $tglAwal, $tglAkhir,
                $tglAwal, $tglAkhir,
                $tglAwal, $tglAkhir,
            ])
            ->withCasts([
                'harga'                     => 'float',
                'stok_awal'                 => 'float',
                'pengadaan'                 => 'float',
                'sub_total_pengadaan'       => 'float',
                'penerimaan'                => 'float',
                'sub_total_penerimaan'      => 'float',
                'stok_keluar'               => 'float',
                'sub_total_keluar'          => 'float',
                'pengambilan_utd'           => 'float',
                'sub_total_pengambilan_utd' => 'float',
                'hibah'                     => 'float',
                'sub_total_hibah'           => 'float',
            ])
            ->join('kodesatuan', 'ipsrsbarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->orderBy('ipsrsbarang.kode_brng', 'asc');
    }
}
