<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;
use App\Models\Farmasi\Inventaris\GudangObat;
use App\Models\Satuan;
use BadMethodCallException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class Obat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kode_brng';

    protected $keyType = 'string';

    protected $table = 'databarang';

    public $incrementing = false;

    public $timestamps = false;

    protected $searchColumns = [
        'kode_brng',
        'nama_brng',
        'kode_satbesar',
        'kode_sat',
        'letak_barang',
        'kdjns',
        'kode_industri',
        'kode_kategori',
        'kode_golongan',
    ];

    public function satuanKecil(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'kode_sat', 'kode_sat');
    }

    public function satuanBesar(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'kode_sat', 'kode_satbesar');
    }

    public function penerimaanDetail(): HasMany
    {
        return $this->hasMany(PenerimaanObatDetail::class, 'kode_brng', 'kode_brng');
    }

    public function penerimaan(): HasManyThrough
    {
        return $this->hasManyThrough(PenerimaanObat::class, PenerimaanObatDetail::class, 'kode_brng', 'no_faktur', 'kode_brng', 'no_faktur');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kode_kategori', 'kode');
    }

    public function golongan(): BelongsTo
    {
        return $this->belongsTo(Golongan::class, 'kode_golongan', 'kode');
    }

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(Jenis::class, 'kdjns', 'kdjns');
    }

    public function mutasi(): HasMany
    {
        return $this->hasMany(MutasiObat::class, 'kode_brng', 'kode_brng');
    }

    public function pemberian(): HasMany
    {
        return $this->hasMany(PemberianObat::class, 'kode_brng', 'kode_brng');
    }

    public function scopeDaruratStok(Builder $query): Builder
    {
        $sqlSelect = <<<'SQL'
            databarang.kode_brng,
            databarang.nama_brng,
            kodesatuan.satuan satuan_kecil,
            kategori_barang.nama kategori,
            databarang.stokminimal,
            ifnull(round(stok_gudang_ifa.stok_di_gudang, 2), 0) stok_sekarang_ifa,
            ifnull(round(stok_gudang_ifi.stok_di_gudang, 2), 0) stok_sekarang_ifi,
            ifnull(round(stok_gudang_ap.stok_di_gudang, 2), 0) stok_sekarang_ap,
            ifnull(round(stok_gudang_ifg.stok_di_gudang, 2), 0) stok_sekarang_ifg,
            (
                ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(current_date(), interval 2 week) and current_date()), 0)
            )
            stok_keluar_medis_14_hari,
            round(databarang.stokminimal - ifnull(stok_gudang_ap.stok_di_gudang, 0), 2) saran_order,
            industrifarmasi.nama_industri,
            round(databarang.h_beli, 2) harga_beli,
            round((databarang.stokminimal - ifnull(stok_gudang_ap.stok_di_gudang, 0)) * databarang.h_beli, 2) harga_beli_total,
            ifnull((select ifnull(round(dp.h_pesan/databarang.isi, 2), 0) from detailpesan dp left join pemesanan p on p.no_faktur = dp.no_faktur where dp.kode_brng = databarang.kode_brng order by p.tgl_pesan desc limit 1), 0) harga_beli_terakhir,
            ifnull((select ifnull(dp.dis, 0) from detailpesan dp left join pemesanan p on p.no_faktur = dp.no_faktur where dp.kode_brng = databarang.kode_brng order by p.tgl_pesan desc limit 1), 0) diskon_terakhir,
            ifnull((select ds.nama_suplier from detailpesan dp left join pemesanan p on p.no_faktur = dp.no_faktur left join datasuplier ds on p.kode_suplier = ds.kode_suplier where dp.kode_brng = databarang.kode_brng order by p.tgl_pesan desc limit 1), '-') supplier_terakhir,
            (
                ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 2 week) and current_date()), 0) + 
                ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 2 week) and current_date()), 0) 
            )
            ke_pasien_14_hari
            SQL;

        $stokGudangIFA = GudangObat::query()
            ->select(['kode_brng', DB::raw('sum(stok) as stok_di_gudang')])
            ->where('kd_bangsal', 'IFA')
            ->groupBy('kode_brng');

        $stokGudangAP = GudangObat::query()
            ->select(['kode_brng', DB::raw('sum(stok) as stok_di_gudang')])
            ->where('kd_bangsal', 'AP')
            ->groupBy('kode_brng');

        $stokGudangIFI = GudangObat::query()
            ->select(['kode_brng', DB::raw('sum(stok) as stok_di_gudang')])
            ->where('kd_bangsal', 'IFI')
            ->groupBy('kode_brng');

        $stokGudangIFG = GudangObat::query()
            ->select(['kode_brng', DB::raw('sum(stok) as stok_di_gudang')])
            ->where('kd_bangsal', 'IFG')
            ->groupBy('kode_brng');

        $this->addSearchConditions([
            'databarang.kode_brng',
            'nama_brng',
            'kodesatuan.satuan',
            'kategori_barang.nama',
            'industrifarmasi.nama_industri',
        ]);

        $this->addSortColumns([
            'satuan_kecil'              => 'kodesatuan.satuan',
            'kategori'                  => 'kategori_barang.nama',
            'stok_sekarang_ifa'         => DB::raw('ifnull(round(stok_gudang_ifa.stok_di_gudang, 2), 0)'),
            'stok_sekarang_ap'          => DB::raw('ifnull(round(stok_gudang_ap.stok_di_gudang, 2), 0)'),
            'stok_sekarang_ifi'         => DB::raw('ifnull(round(stok_gudang_ifi.stok_di_gudang, 2), 0)'),
            'stok_sekarang_ifg'         => DB::raw('ifnull(round(stok_gudang_ifg.stok_di_gudang, 2), 0)'),
            'stok_keluar_medis_14_hari' => DB::raw('(ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(current_date(), interval 2 week) and current_date()), 0))'),
            'saran_order'               => DB::raw('(databarang.stokminimal - ifnull(stok_gudang_ap.stok_di_gudang, 0))'),
            'harga_beli'                => DB::raw('round(databarang.h_beli)'),
            'harga_beli_total'          => DB::raw('round((databarang.stokminimal - ifnull(stok_gudang_ap.stok_di_gudang, 0)) * databarang.h_beli)'),
            'harga_beli_terakhir'       => DB::raw('(select ifnull(round(dp.h_pesan / databarang.isi, 2), 0) from detailpesan dp left join pemesanan p on p.no_faktur = dp.no_faktur where dp.kode_brng = databarang.kode_brng order by p.tgl_pesan desc limit 1)'),
            'diskon_terakhir'           => DB::raw("(select ifnull(dp.dis, '0') from detailpesan dp left join pemesanan p on p.no_faktur = dp.no_faktur where dp.kode_brng = databarang.kode_brng order by p.tgl_pesan desc limit 1)"),
            'supplier_terakhir'         => DB::raw("(select ifnull(ds.nama_suplier, '-') from detailpesan dp left join pemesanan p on p.no_faktur = dp.no_faktur left join datasuplier ds on p.kode_suplier = ds.kode_suplier where dp.kode_brng = databarang.kode_brng order by p.tgl_pesan desc limit 1)"),
            'ke_pasien_14_hari'         => DB::raw('(ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 2 week) and current_date()), 0) + ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 2 week) and current_date()), 0))'),
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts([
                'stokminimal'               => 'float',
                'stok_sekarang_ifa'         => 'float',
                'stok_sekarang_ifi'         => 'float',
                'stok_sekarang_ap'          => 'float',
                'stok_sekarang_ifg'         => 'float',
                'stok_keluar_medis_14_hari' => 'float',
                'saran_order'               => 'float',
                'harga_beli'                => 'float',
                'harga_beli_total'          => 'float',
                'harga_beli_terakhir'       => 'float',
                'diskon_terakhir'           => 'float',
                'ke_pasien_14_hari'         => 'float',
            ])
            ->join('kategori_barang', 'databarang.kode_kategori', '=', 'kategori_barang.kode')
            ->join('kodesatuan', 'databarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->join('industrifarmasi', 'databarang.kode_industri', '=', 'industrifarmasi.kode_industri')
            ->leftJoinSub($stokGudangIFA, 'stok_gudang_ifa', fn (JoinClause $join) => $join->on('databarang.kode_brng', '=', 'stok_gudang_ifa.kode_brng'))
            ->leftJoinSub($stokGudangAP, 'stok_gudang_ap', fn (JoinClause $join) => $join->on('databarang.kode_brng', '=', 'stok_gudang_ap.kode_brng'))
            ->leftJoinSub($stokGudangIFI, 'stok_gudang_ifi', fn (JoinClause $join) => $join->on('databarang.kode_brng', '=', 'stok_gudang_ifi.kode_brng'))
            ->leftJoinSub($stokGudangIFG, 'stok_gudang_ifg', fn (JoinClause $join) => $join->on('databarang.kode_brng', '=', 'stok_gudang_ifg.kode_brng'))
            ->where('databarang.status', '1')
            ->where('databarang.stokminimal', '>', 0)
            ->whereRaw('(databarang.stokminimal - ifnull(stok_gudang_ap.stok_di_gudang, 0)) > 0')
            ->whereRaw('ifnull(stok_gudang_ap.stok_di_gudang, 0) <= databarang.stokminimal')
            ->orderBy('databarang.nama_brng');
    }

    public function scopePemakaianStok(Builder $query): Builder
    {
        $sqlSelect = <<<'SQL'
            databarang.kode_brng,
            databarang.nama_brng,
            kodesatuan.satuan satuan_kecil,
            kategori_barang.nama kategori,
            (ifnull((select round(sum(gudangbarang.stok), 2) from gudangbarang where gudangbarang.kode_brng = databarang.kode_brng), 0)) stok_saat_ini,
            (
                ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 2 week) and current_date()), 0) + 
                ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 2 week) and current_date()), 0) 
            ) ke_pasien_14_hari,
            (
                ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 1 week) and current_date()), 0) + 
                ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 1 week) and current_date()), 0) +
                ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(current_date(), interval 1 week) and current_date()), 0) 
            ) pemakaian_1_minggu,
            (
                ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 1 month) and current_date()), 0) + 
                ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 1 month) and current_date()), 0) +
                ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(current_date(), interval 1 month) and current_date()), 0) 
            ) pemakaian_1_bulan,
            (
                ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 3 month) and current_date()), 0) + 
                ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 3 month) and current_date()), 0) +
                ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(current_date(), interval 3 month) and current_date()), 0) 
            ) pemakaian_3_bulan,
            (
                ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 6 month) and current_date()), 0) + 
                ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 6 month) and current_date()), 0) +
                ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(current_date(), interval 6 month) and current_date()), 0)
            ) pemakaian_6_bulan,
            (
                ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 10 month) and current_date()), 0) + 
                ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 10 month) and current_date()), 0) +
                ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(current_date(), interval 10 month) and current_date()), 0) 
            ) pemakaian_10_bulan,
            (
                ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 1 year) and current_date()), 0) + 
                ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 1 year) and current_date()), 0) +
                ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(current_date(), interval 1 year) and current_date()), 0) 
            ) pemakaian_12_bulan
            SQL;

        $this->addSearchConditions([
            'databarang.kode_brng',
            'nama_brng',
            'kodesatuan.satuan',
            'kategori_barang.nama',
        ]);

        $this->addRawColumns([
            'satuan_kecil'         => 'kodesatuan.satuan',
            'kategori'             => 'kategori_barang.nama',
            'stok_saat_ini'        => DB::raw('(ifnull((select round(sum(gudangbarang.stok), 2) from gudangbarang where gudangbarang.kode_brng = databarang.kode_brng), 0))'),
            'ke_pasien_14_hari'    => DB::raw('(ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 2 week) and current_date()), 0) + ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 2 week) and current_date()), 0))'),
            'pemakaian_1_minggu'   => DB::raw('(ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 1 week) and current_date()), 0) + ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 1 week) and current_date()), 0) + ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(current_date(), interval 1 week) and current_date()), 0))'),
            'pemakaian_1_bulan'    => DB::raw('(ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 1 month) and current_date()), 0) + ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 1 month) and current_date()), 0) + ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(current_date(), interval 1 month) and current_date()), 0))'),
            'pemakaian_3_bulan'    => DB::raw('(ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 3 month) and current_date()), 0) + ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 3 month) and current_date()), 0) + ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(current_date(), interval 3 month) and current_date()), 0))'),
            'pemakaian_6_bulan'    => DB::raw('(ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 6 month) and current_date()), 0) + ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 6 month) and current_date()), 0) + ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(current_date(), interval 6 month) and current_date()), 0))'),
            'pemakaian_10_bulan'   => DB::raw('(ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 10 month) and current_date()), 0) +  ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 10 month) and current_date()), 0) + ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(current_date(), interval 10 month) and current_date()), 0))'),
            'pemakaian_12_bulan'   => DB::raw('(ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 1 year) and current_date()), 0) +  ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 1 year) and current_date()), 0) + ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(current_date(), interval 1 year) and current_date()), 0))'),
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts([
                'stok_saat_ini'       => 'float',
                'ke_pasien_14_hari'   => 'float',
                'pemakaian_1_minggu'  => 'float',
                'pemakaian_1_bulan'   => 'float',
                'pemakaian_3_bulan'   => 'float',
                'pemakaian_6_bulan'   => 'float',
                'pemakaian_10_bulan'  => 'float',
                'pemakaian_12_bulan'  => 'float',
            ])
            ->join('kategori_barang', 'databarang.kode_kategori', '=', 'kategori_barang.kode')
            ->join('kodesatuan', 'databarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->where('databarang.status', '1')
            ->orderBy('databarang.nama_brng');
    }

    /**
     * @psalm-param  "narkotika"|"psikotropika" $golongan
     */
    public function scopePemakaianObatNAPZA(Builder $query, string $tglAwal = '', string $tglAkhir = '', string $golongan = 'narkotika'): Builder
    {
        if (! in_array($golongan, ['narkotika', 'psikotropika'])) {
            throw new BadMethodCallException('Invalid value provied for parameter [golongan]');
        }

        $tglAwal = carbon($tglAwal)->startOfMonth()->toDateString();
        $tglAkhir = carbon($tglAkhir)->endOfMonth()->toDateString();

        $sqlSelect = <<<'SQL'
            databarang.kode_brng,
            databarang.nama_brng,
            databarang.kode_golongan,
            golongan_barang.nama,
            kodesatuan.satuan,
            ifnull((select riwayat_barang_medis.stok_awal from riwayat_barang_medis where riwayat_barang_medis.kode_brng = databarang.kode_brng and riwayat_barang_medis.kd_bangsal = 'AP' and riwayat_barang_medis.tanggal between ? and ? order by riwayat_barang_medis.tanggal asc, riwayat_barang_medis.jam asc limit 1), (select riwayat_barang_medis.stok_akhir from riwayat_barang_medis where riwayat_barang_medis.kode_brng = databarang.kode_brng and riwayat_barang_medis.kd_bangsal = 'AP' and riwayat_barang_medis.tanggal < ? order by riwayat_barang_medis.tanggal desc, riwayat_barang_medis.jam desc limit 1)) as stok_awal,
            (select sum(mutasibarang.jml) from mutasibarang where mutasibarang.kode_brng = databarang.kode_brng and mutasibarang.kd_bangsalke = 'AP' and date(mutasibarang.tanggal) between ? and ?) tf_masuk,
            (select sum(detailpesan.jumlah2) from detailpesan join pemesanan on detailpesan.no_faktur = pemesanan.no_faktur where detailpesan.kode_brng = databarang.kode_brng and pemesanan.kd_bangsal = 'AP' and pemesanan.tgl_pesan between ? and ?) penerimaan_obat,
            (select sum(riwayat_barang_medis.masuk) from riwayat_barang_medis where riwayat_barang_medis.kode_brng = databarang.kode_brng and riwayat_barang_medis.kd_bangsal = 'AP' and riwayat_barang_medis.posisi = 'Piutang' and riwayat_barang_medis.tanggal between ? and ?) piutang_masuk,
            (select sum(detailhibah_obat_bhp.jumlah2) from detailhibah_obat_bhp join hibah_obat_bhp on detailhibah_obat_bhp.no_hibah = hibah_obat_bhp.no_hibah where detailhibah_obat_bhp.kode_brng = databarang.kode_brng and hibah_obat_bhp.kd_bangsal = 'AP' and hibah_obat_bhp.tgl_hibah between ? and ?) hibah_obat,
            (select sum(detreturjual.jml_retur) from detreturjual join returjual on detreturjual.no_retur_jual = returjual.no_retur_jual where detreturjual.kode_brng = databarang.kode_brng and returjual.kd_bangsal = 'AP' and returjual.tgl_retur between ? and ?) retur_pasien,
            (select sum(riwayat_barang_medis.masuk) from riwayat_barang_medis where riwayat_barang_medis.posisi = 'Pemberian Obat' and riwayat_barang_medis.status = 'hapus' and riwayat_barang_medis.kode_brng = databarang.kode_brng and kd_bangsal = 'AP' and tanggal between ? and ?) hapus_beriobat,
            (select sum(detail_pemberian_obat.jml) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and kd_bangsal = 'AP' and tgl_perawatan between ? and ?) pemberian_obat,
            (select sum(detailjual.jumlah) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.kd_bangsal = 'AP' and penjualan.tgl_jual between ? and ?) penjualan_obat,
            (select sum(riwayat_barang_medis.keluar) from riwayat_barang_medis where riwayat_barang_medis.kode_brng = databarang.kode_brng and riwayat_barang_medis.kd_bangsal = 'AP' and riwayat_barang_medis.posisi = 'Piutang' and riwayat_barang_medis.tanggal between ? and ?) piutang_keluar,
            (select sum(mutasibarang.jml) from mutasibarang where mutasibarang.kode_brng = databarang.kode_brng and mutasibarang.kd_bangsaldari = 'AP' and date(mutasibarang.tanggal) between ? and ?) tf_keluar,
            (select sum(detreturbeli.jml_retur2) from detreturbeli join returbeli on detreturbeli.no_retur_beli = returbeli.no_retur_beli where detreturbeli.kode_brng = databarang.kode_brng and returbeli.kd_bangsal = 'AP' and returbeli.tgl_retur between ? and ?) retur_supplier
            SQL;

        $this->addSearchConditions([
            'golongan_barang.nama',
            'kodesatuan.kode_sat',
            'kodesatuan.satuan',
        ]);

        return $query
            ->selectRaw($sqlSelect, [
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
                $tglAwal, $tglAkhir,
            ])
            ->withCasts([
                'stok_awal'          => 'float',
                'stok_awal_terakhir' => 'float',
                'tf_masuk'           => 'float',
                'penerimaan_obat'    => 'float',
                'hibah_obat'         => 'float',
                'retur_pasien'       => 'float',
                'pemberian_obat'     => 'float',
                'penjualan_obat'     => 'float',
                'tf_keluar'          => 'float',
                'retur_supplier'     => 'float',
            ])
            ->join('golongan_barang', 'databarang.kode_golongan', '=', 'golongan_barang.kode')
            ->join('kodesatuan', 'databarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->where('databarang.status', '1')
            ->where(fn (Builder $query): Builder => $query
                ->when($golongan === 'narkotika', fn (Builder $q): Builder => $q->where('databarang.kode_golongan', 'G07'))
                ->when($golongan === 'psikotropika', fn (Builder $q): Builder => $q->where('databarang.kode_golongan', 'G01')));
    }

    public function scopeDaftarRiwayat(
        Builder $query,
        string $kategori = 'obat',
        string $tglAwal = '',
        string $tglAkhir = '',
        bool $hanyaTampilkanYangNol = false): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->subYear()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            databarang.kode_brng,
            databarang.nama_brng,
            ifnull((select stok_akhir from riwayat_barang_medis where riwayat_barang_medis.kode_brng = databarang.kode_brng and riwayat_barang_medis.tanggal between ? and ? order by tanggal desc, jam desc limit 1), "-") stok_akhir,
            ifnull((select masuk from riwayat_barang_medis where riwayat_barang_medis.kode_brng = databarang.kode_brng and masuk != 0 and riwayat_barang_medis.tanggal between ? and ? order by tanggal desc, jam desc limit 1), "-") order_terakhir,
            ifnull((select concat(riwayat_barang_medis.tanggal, ' ', riwayat_barang_medis.jam) from riwayat_barang_medis where riwayat_barang_medis.kode_brng = databarang.kode_brng and masuk != 0 and riwayat_barang_medis.tanggal between ? and ? order by tanggal desc, jam desc limit 1), "-")  tanggal_order_terakhir,
            ifnull((select posisi from riwayat_barang_medis where riwayat_barang_medis.kode_brng = databarang.kode_brng and masuk != 0 and riwayat_barang_medis.tanggal between ? and ? order by tanggal desc, jam desc limit 1), "-") posisi_order_terakhir,
            ifnull((select keluar from riwayat_barang_medis where riwayat_barang_medis.kode_brng = databarang.kode_brng and keluar != 0 and riwayat_barang_medis.tanggal between ? and ? order by tanggal desc, jam desc limit 1), "-") penggunaan_terakhir,
            ifnull((select concat (riwayat_barang_medis.tanggal, ' ', riwayat_barang_medis.jam) from riwayat_barang_medis where riwayat_barang_medis.kode_brng = databarang.kode_brng and keluar != 0 and riwayat_barang_medis.tanggal between ? and ? order by tanggal desc, jam desc limit 1), "-") tanggal_penggunaan_terakhir,
            ifnull((select posisi from riwayat_barang_medis where riwayat_barang_medis.kode_brng = databarang.kode_brng and keluar != 0 and riwayat_barang_medis.tanggal between ? and ? order by tanggal desc, jam desc limit 1), "-") posisi_penggunaan_terakhir
            SQL;

        $this->addSearchConditions([
            'databarang.kode_brng',
            'nama_brng',
        ]);

        return $query
            ->selectRaw($sqlSelect, [
                $tglAwal, $tglAkhir,
                $tglAwal, $tglAkhir,
                $tglAwal, $tglAkhir,
                $tglAwal, $tglAkhir,
                $tglAwal, $tglAkhir,
                $tglAwal, $tglAkhir,
                $tglAwal, $tglAkhir,
            ])
            ->join('riwayat_barang_medis', 'databarang.kode_brng', '=', 'riwayat_barang_medis.kode_brng')
            ->withCasts(['stok_akhir' => 'float', 'order_terakhir' => 'float', 'penggunaan_terakhir' => 'float'])
            ->whereBetween('riwayat_barang_medis.tanggal', [$tglAwal, $tglAkhir])
            ->whereIn('riwayat_barang_medis.kd_bangsal', ['AP', 'IFA', 'IFG', 'IFI'])
            ->where(fn (Builder $query): Builder => $query
                ->when($kategori === 'obat', fn (Builder $q): Builder => $q->where('databarang.kode_kategori', 'like', '2.%'))
                ->when($kategori === 'alkes', fn (Builder $q): Builder => $q->where('databarang.kode_kategori', 'like', '3.%')))
            ->when($hanyaTampilkanYangNol, fn (Builder $query) => $query
                ->whereRaw('ifnull((select stok_akhir from riwayat_barang_medis where riwayat_barang_medis.kode_brng = databarang.kode_brng and riwayat_barang_medis.tanggal between ? and ? order by tanggal desc, jam desc limit 1), 0) = 0', [$tglAwal, $tglAkhir]))
            ->orderBy('riwayat_barang_medis.tanggal', 'desc')
            ->orderBy('riwayat_barang_medis.jam', 'desc')
            ->groupBy('databarang.kode_brng');
    }
}
