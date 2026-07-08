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

    /**
     * Daftar gudang farmasi yang dimonitor. Tambahkan/kurangi di sini
     * jika suatu saat cakupan lokasi berubah.
     */
    private const GUDANG_FARMASI = [
        'GF'  => 'GUDANG FARMASI',
        'IFA' => 'FARMASI A',
        'AP'  => 'APOTEK/INSTALASI FARMASI',
        'IFC' => 'INSTALASI FARMASI CATHLAB',
        'IFO' => 'INSTALASI FARMASI OK',
        'IFI' => 'INSTALASI FARMASI RAWAT INAP',
        'IFG' => 'INSTALASI FARMASI IGD',
    ];

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

    public function scopeDaruratStok(Builder $query, string $tanggal = ''): Builder
    {
        $sqlSelect = <<<'SQL'
            databarang.kode_brng,
            databarang.nama_brng,
            kodesatuan.satuan satuan_kecil,
            kategori_barang.nama kategori,
            databarang.stokminimal,
            ifnull(round(stok_gudang_ifi.stok_di_gudang, 2), 0) stok_sekarang_ifi,
            ifnull(round(stok_gudang_ap.stok_di_gudang, 2), 0) stok_sekarang_ap,
            ifnull(round(stok_gudang_ifg.stok_di_gudang, 2), 0) stok_sekarang_ifg,
            ifnull(round(stok_gudang_gf.stok_di_gudang, 2), 0) stok_sekarang_gf,
            ifnull(round(stok_gudang_ifo.stok_di_gudang, 2), 0) stok_sekarang_ifo,
            (ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between ? and current_date()), 0)) stok_keluar_medis_14_hari,
            (ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between ? and current_date()), 0) + ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between ? and current_date()), 0)) ke_pasien_14_hari,
            (ifnull((select round(sum(dp.jumlah), 2) from piutang p join detailpiutang dp on p.nota_piutang = dp.nota_piutang where dp.kode_brng = databarang.kode_brng and p.tgl_piutang between ? and current_date()), 0)) piutang_14_hari,
            round(databarang.stokminimal - ifnull(stok_gudang_gf.stok_di_gudang, 0), 2) saran_order,
            industrifarmasi.nama_industri,
            round(databarang.h_beli, 2) harga_beli,
            round((databarang.stokminimal - ifnull(stok_gudang_gf.stok_di_gudang, 0)) * databarang.h_beli, 2) harga_beli_total,
            ifnull((select ifnull(round(dp.h_pesan/databarang.isi, 2), 0) from detailpesan dp left join pemesanan p on p.no_faktur = dp.no_faktur where dp.kode_brng = databarang.kode_brng order by p.tgl_pesan desc limit 1), 0) harga_beli_terakhir,
            ifnull((select ifnull(dp.dis, 0) from detailpesan dp left join pemesanan p on p.no_faktur = dp.no_faktur where dp.kode_brng = databarang.kode_brng order by p.tgl_pesan desc limit 1), 0) diskon_terakhir,
            ifnull((select ds.nama_suplier from detailpesan dp left join pemesanan p on p.no_faktur = dp.no_faktur left join datasuplier ds on p.kode_suplier = ds.kode_suplier where dp.kode_brng = databarang.kode_brng order by p.tgl_pesan desc limit 1), '-') supplier_terakhir
            SQL;

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

        $stokGudangGF = GudangObat::query()
            ->select(['kode_brng', DB::raw('sum(stok) as stok_di_gudang')])
            ->where('kd_bangsal', 'GF')
            ->groupBy('kode_brng');

        $stokGudangIFO = GudangObat::query()
            ->select(['kode_brng', DB::raw('sum(stok) as stok_di_gudang')])
            ->where('kd_bangsal', 'IFO')
            ->groupBy('kode_brng');

        $this->addSearchConditions([
            'databarang.kode_brng',
            'nama_brng',
            'kodesatuan.satuan',
            'kategori_barang.nama',
            'industrifarmasi.nama_industri',
        ]);

        $this->addSortColumns([
            'satuan_kecil'               => 'kodesatuan.satuan',
            'kategori'                   => 'kategori_barang.nama',
            'stok_sekarang_ifa'          => DB::raw('ifnull(round(stok_gudang_ifa.stok_di_gudang, 2), 0)'),
            'stok_sekarang_ap'           => DB::raw('ifnull(round(stok_gudang_ap.stok_di_gudang, 2), 0)'),
            'stok_sekarang_ifi'          => DB::raw('ifnull(round(stok_gudang_ifi.stok_di_gudang, 2), 0)'),
            'stok_sekarang_ifg'          => DB::raw('ifnull(round(stok_gudang_ifg.stok_di_gudang, 2), 0)'),
            'stok_sekarang_gf'           => DB::raw('ifnull(round(stok_gudang_gf.stok_di_gudang, 2), 0)'),
            'stok_sekarang_ifo'          => DB::raw('ifnull(round(stok_gudang_ifo.stok_di_gudang, 2), 0)'),
            'stok_keluar_medis_14_hari'  => 'stok_keluar_medis_14_hari',
            'ke_pasien_14_hari'          => 'ke_pasien_14_hari',
            'piutang_14_hari'            => 'piutang_14_hari',
            'saran_order'                => DB::raw('(databarang.stokminimal - ifnull(stok_gudang_ap.stok_di_gudang, 0))'),
            'harga_beli'                 => DB::raw('round(databarang.h_beli)'),
            'harga_beli_total'           => DB::raw('round((databarang.stokminimal - ifnull(stok_gudang_ap.stok_di_gudang, 0)) * databarang.h_beli)'),
            'harga_beli_terakhir'        => DB::raw('(select ifnull(round(dp.h_pesan / databarang.isi, 2), 0) from detailpesan dp left join pemesanan p on p.no_faktur = dp.no_faktur where dp.kode_brng = databarang.kode_brng order by p.tgl_pesan desc limit 1)'),
            'diskon_terakhir'            => DB::raw("(select ifnull(dp.dis, '0') from detailpesan dp left join pemesanan p on p.no_faktur = dp.no_faktur where dp.kode_brng = databarang.kode_brng order by p.tgl_pesan desc limit 1)"),
            'supplier_terakhir'          => DB::raw("(select ifnull(ds.nama_suplier, '-') from detailpesan dp left join pemesanan p on p.no_faktur = dp.no_faktur left join datasuplier ds on p.kode_suplier = ds.kode_suplier where dp.kode_brng = databarang.kode_brng order by p.tgl_pesan desc limit 1)"),
        ]);

        return $query
            ->selectRaw($sqlSelect, [
                $tanggal,
                $tanggal,
                $tanggal,
                $tanggal,
            ])
            ->withCasts([
                'stokminimal'                => 'float',
                'stok_sekarang_ifi'          => 'float',
                'stok_sekarang_ap'           => 'float',
                'stok_sekarang_ifg'          => 'float',
                'stok_sekarang_gf'           => 'float',
                'stok_sekarang_ifo'          => 'float',
                'stok_keluar_medis_14_hari'  => 'float',
                'ke_pasien_14_hari'          => 'float',
                'piutang_14_hari'            => 'float',
                'total_stok_sekarang'        => 'float',
                'total_keluar_14_hari'       => 'float',
                'saran_order'                => 'float',
                'harga_beli'                 => 'float',
                'harga_beli_total'           => 'float',
                'harga_beli_terakhir'        => 'float',
                'diskon_terakhir'            => 'float',
            ])
            ->join('kategori_barang', 'databarang.kode_kategori', '=', 'kategori_barang.kode')
            ->join('kodesatuan', 'databarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->join('industrifarmasi', 'databarang.kode_industri', '=', 'industrifarmasi.kode_industri')
            ->leftJoinSub($stokGudangAP, 'stok_gudang_ap', fn (JoinClause $join) => $join->on('databarang.kode_brng', '=', 'stok_gudang_ap.kode_brng'))
            ->leftJoinSub($stokGudangIFI, 'stok_gudang_ifi', fn (JoinClause $join) => $join->on('databarang.kode_brng', '=', 'stok_gudang_ifi.kode_brng'))
            ->leftJoinSub($stokGudangIFG, 'stok_gudang_ifg', fn (JoinClause $join) => $join->on('databarang.kode_brng', '=', 'stok_gudang_ifg.kode_brng'))
            ->leftJoinSub($stokGudangGF, 'stok_gudang_gf', fn (JoinClause $join) => $join->on('databarang.kode_brng', '=', 'stok_gudang_gf.kode_brng'))
            ->leftJoinSub($stokGudangIFO, 'stok_gudang_ifo', fn (JoinClause $join) => $join->on('databarang.kode_brng', '=', 'stok_gudang_ifo.kode_brng'))
            ->where('databarang.status', '1')
            ->where('databarang.stokminimal', '>', 0)
            ->whereRaw('(databarang.stokminimal - ifnull(stok_gudang_gf.stok_di_gudang, 0)) > 0')
            ->whereRaw('ifnull(stok_gudang_gf.stok_di_gudang, 0) <= databarang.stokminimal')
            ->orderBy('databarang.nama_brng');
    }

    public function scopePemakaianStok(Builder $query): Builder
    {
        $sqlSelect = <<<'SQL'
            databarang.kode_brng,
            databarang.nama_brng,
            kodesatuan.satuan satuan_kecil,
            kategori_barang.nama kategori,
            databarang.h_beli,
            databarang.ralan,
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
            throw new BadMethodCallException('Invalid value provided for parameter [golongan]');
        }

        $tglAwal = carbon($tglAwal)->startOfMonth()->toDateString();
        $tglAkhir = carbon($tglAkhir)->endOfMonth()->toDateString();
        $kodeGudang = array_keys(self::GUDANG_FARMASI);

        $conn = $this->getConnectionName();

        $tfMasuk = DB::connection($conn)->table('mutasibarang')
            ->select('kode_brng', 'kd_bangsalke', DB::raw('SUM(jml) as total'))
            ->whereIn('kd_bangsalke', $kodeGudang)
            ->whereBetween('tanggal', [$tglAwal, $tglAkhir])
            ->groupBy('kode_brng', 'kd_bangsalke');

        $penerimaan = DB::connection($conn)->table('detailpesan')
            ->join('pemesanan', 'detailpesan.no_faktur', '=', 'pemesanan.no_faktur')
            ->select('detailpesan.kode_brng', 'pemesanan.kd_bangsal', DB::raw('SUM(detailpesan.jumlah2) as total'))
            ->whereIn('pemesanan.kd_bangsal', $kodeGudang)
            ->whereBetween('pemesanan.tgl_pesan', [$tglAwal, $tglAkhir])
            ->groupBy('detailpesan.kode_brng', 'pemesanan.kd_bangsal');

        $piutangMasuk = DB::connection($conn)->table('riwayat_barang_medis')
            ->select('kode_brng', 'kd_bangsal', DB::raw('SUM(masuk) as total'))
            ->where('posisi', 'Piutang')
            ->whereIn('kd_bangsal', $kodeGudang)
            ->whereBetween('tanggal', [$tglAwal, $tglAkhir])
            ->groupBy('kode_brng', 'kd_bangsal');

        $hibah = DB::connection($conn)->table('detailhibah_obat_bhp')
            ->join('hibah_obat_bhp', 'detailhibah_obat_bhp.no_hibah', '=', 'hibah_obat_bhp.no_hibah')
            ->select('detailhibah_obat_bhp.kode_brng', 'hibah_obat_bhp.kd_bangsal', DB::raw('SUM(detailhibah_obat_bhp.jumlah2) as total'))
            ->whereIn('hibah_obat_bhp.kd_bangsal', $kodeGudang)
            ->whereBetween('hibah_obat_bhp.tgl_hibah', [$tglAwal, $tglAkhir])
            ->groupBy('detailhibah_obat_bhp.kode_brng', 'hibah_obat_bhp.kd_bangsal');

        $returPasien = DB::connection($conn)->table('detreturjual')
            ->join('returjual', 'detreturjual.no_retur_jual', '=', 'returjual.no_retur_jual')
            ->select('detreturjual.kode_brng', 'returjual.kd_bangsal', DB::raw('SUM(detreturjual.jml_retur) as total'))
            ->whereIn('returjual.kd_bangsal', $kodeGudang)
            ->whereBetween('returjual.tgl_retur', [$tglAwal, $tglAkhir])
            ->groupBy('detreturjual.kode_brng', 'returjual.kd_bangsal');

        $hapusBeriObat = DB::connection($conn)->table('riwayat_barang_medis')
            ->select('kode_brng', 'kd_bangsal', DB::raw('SUM(masuk) as total'))
            ->where('posisi', 'Pemberian Obat')
            ->where('status', 'hapus')
            ->whereIn('kd_bangsal', $kodeGudang)
            ->whereBetween('tanggal', [$tglAwal, $tglAkhir])
            ->groupBy('kode_brng', 'kd_bangsal');

        $pemberianObat = DB::connection($conn)->table('detail_pemberian_obat')
            ->select('kode_brng', 'kd_bangsal', DB::raw('SUM(jml) as total'))
            ->whereIn('kd_bangsal', $kodeGudang)
            ->whereBetween('tgl_perawatan', [$tglAwal, $tglAkhir])
            ->groupBy('kode_brng', 'kd_bangsal');

        $penjualanObat = DB::connection($conn)->table('detailjual')
            ->join('penjualan', 'detailjual.nota_jual', '=', 'penjualan.nota_jual')
            ->select('detailjual.kode_brng', 'penjualan.kd_bangsal', DB::raw('SUM(detailjual.jumlah) as total'))
            ->whereIn('penjualan.kd_bangsal', $kodeGudang)
            ->whereBetween('penjualan.tgl_jual', [$tglAwal, $tglAkhir])
            ->groupBy('detailjual.kode_brng', 'penjualan.kd_bangsal');

        $piutangKeluar = DB::connection($conn)->table('riwayat_barang_medis')
            ->select('kode_brng', 'kd_bangsal', DB::raw('SUM(keluar) as total'))
            ->where('posisi', 'Piutang')
            ->whereIn('kd_bangsal', $kodeGudang)
            ->whereBetween('tanggal', [$tglAwal, $tglAkhir])
            ->groupBy('kode_brng', 'kd_bangsal');

        $tfKeluar = DB::connection($conn)->table('mutasibarang')
            ->select('kode_brng', 'kd_bangsaldari', DB::raw('SUM(jml) as total'))
            ->whereIn('kd_bangsaldari', $kodeGudang)
            ->whereBetween('tanggal', [$tglAwal, $tglAkhir])
            ->groupBy('kode_brng', 'kd_bangsaldari');

        $returSupplier = DB::connection($conn)->table('detreturbeli')
            ->join('returbeli', 'detreturbeli.no_retur_beli', '=', 'returbeli.no_retur_beli')
            ->select('detreturbeli.kode_brng', 'returbeli.kd_bangsal', DB::raw('SUM(detreturbeli.jml_retur2) as total'))
            ->whereIn('returbeli.kd_bangsal', $kodeGudang)
            ->whereBetween('returbeli.tgl_retur', [$tglAwal, $tglAkhir])
            ->groupBy('detreturbeli.kode_brng', 'returbeli.kd_bangsal');

        $subBangsal = DB::connection($conn)->table('bangsal')
            ->select(['kd_bangsal', 'nm_bangsal'])
            ->whereIn('kd_bangsal', $kodeGudang);

        $this->addSearchConditions([
            'golongan_barang.nama',
            'kodesatuan.kode_sat',
            'kodesatuan.satuan',
        ]);

        $sqlSelect = <<<'SQL'
            databarang.kode_brng,
            databarang.nama_brng,
            databarang.kode_golongan,
            golongan_barang.nama,
            kodesatuan.satuan,
            gudang.kd_bangsal,
            gudang.nm_bangsal,
            (select r1.stok_awal from riwayat_barang_medis r1 where r1.kode_brng = databarang.kode_brng and r1.kd_bangsal = gudang.kd_bangsal and r1.tanggal between ? and ? order by r1.tanggal asc, r1.jam asc limit 1) stok_awal,
            (select r2.stok_akhir from riwayat_barang_medis r2 where r2.kode_brng = databarang.kode_brng and r2.kd_bangsal = gudang.kd_bangsal and r2.tanggal < ? order by r2.tanggal desc, r2.jam desc limit 1) stok_awal_terakhir,
            ifnull(tf_masuk.total, 0) tf_masuk,
            ifnull(penerimaan.total, 0) penerimaan_obat,
            ifnull(piutang_masuk.total, 0) piutang_masuk,
            ifnull(hibah.total, 0) hibah_obat,
            ifnull(retur_pasien.total, 0) retur_pasien,
            ifnull(hapus_beriobat.total, 0) hapus_beriobat,
            ifnull(pemberian_obat.total, 0) pemberian_obat,
            ifnull(penjualan_obat.total, 0) penjualan_obat,
            ifnull(piutang_keluar.total, 0) piutang_keluar,
            ifnull(tf_keluar.total, 0) tf_keluar,
            ifnull(retur_supplier.total, 0) retur_supplier
        SQL;

        $this->addRawColumns([
            'stok_awal'          => DB::raw('(select r1.stok_awal from riwayat_barang_medis r1 where r1.kode_brng = databarang.kode_brng and r1.kd_bangsal = gudang.kd_bangsal and r1.tanggal between ? and ? order by r1.tanggal asc, r1.jam asc limit 1)'),
            'stok_awal_terakhir' => DB::raw('(select r2.stok_akhir from riwayat_barang_medis r2 where r2.kode_brng = databarang.kode_brng and r2.kd_bangsal = gudang.kd_bangsal and r2.tanggal < ? order by r2.tanggal desc, r2.jam desc limit 1)'),
        ]);

        return $query
            ->selectRaw($sqlSelect, [
                $tglAwal, $tglAkhir,
                $tglAwal,
            ])
            ->joinSub($subBangsal, 'gudang', fn ($join) => $join->whereRaw('1 = 1'))
            ->leftJoinSub($tfMasuk, 'tf_masuk', fn ($j) => $j->on('databarang.kode_brng', '=', 'tf_masuk.kode_brng')->on('gudang.kd_bangsal', '=', 'tf_masuk.kd_bangsalke'))
            ->leftJoinSub($penerimaan, 'penerimaan', fn ($j) => $j->on('databarang.kode_brng', '=', 'penerimaan.kode_brng')->on('gudang.kd_bangsal', '=', 'penerimaan.kd_bangsal'))
            ->leftJoinSub($piutangMasuk, 'piutang_masuk', fn ($j) => $j->on('databarang.kode_brng', '=', 'piutang_masuk.kode_brng')->on('gudang.kd_bangsal', '=', 'piutang_masuk.kd_bangsal'))
            ->leftJoinSub($hibah, 'hibah', fn ($j) => $j->on('databarang.kode_brng', '=', 'hibah.kode_brng')->on('gudang.kd_bangsal', '=', 'hibah.kd_bangsal'))
            ->leftJoinSub($returPasien, 'retur_pasien', fn ($j) => $j->on('databarang.kode_brng', '=', 'retur_pasien.kode_brng')->on('gudang.kd_bangsal', '=', 'retur_pasien.kd_bangsal'))
            ->leftJoinSub($hapusBeriObat, 'hapus_beriobat', fn ($j) => $j->on('databarang.kode_brng', '=', 'hapus_beriobat.kode_brng')->on('gudang.kd_bangsal', '=', 'hapus_beriobat.kd_bangsal'))
            ->leftJoinSub($pemberianObat, 'pemberian_obat', fn ($j) => $j->on('databarang.kode_brng', '=', 'pemberian_obat.kode_brng')->on('gudang.kd_bangsal', '=', 'pemberian_obat.kd_bangsal'))
            ->leftJoinSub($penjualanObat, 'penjualan_obat', fn ($j) => $j->on('databarang.kode_brng', '=', 'penjualan_obat.kode_brng')->on('gudang.kd_bangsal', '=', 'penjualan_obat.kd_bangsal'))
            ->leftJoinSub($piutangKeluar, 'piutang_keluar', fn ($j) => $j->on('databarang.kode_brng', '=', 'piutang_keluar.kode_brng')->on('gudang.kd_bangsal', '=', 'piutang_keluar.kd_bangsal'))
            ->leftJoinSub($tfKeluar, 'tf_keluar', fn ($j) => $j->on('databarang.kode_brng', '=', 'tf_keluar.kode_brng')->on('gudang.kd_bangsal', '=', 'tf_keluar.kd_bangsaldari'))
            ->leftJoinSub($returSupplier, 'retur_supplier', fn ($j) => $j->on('databarang.kode_brng', '=', 'retur_supplier.kode_brng')->on('gudang.kd_bangsal', '=', 'retur_supplier.kd_bangsal'))
            ->join('golongan_barang', 'databarang.kode_golongan', '=', 'golongan_barang.kode')
            ->join('kodesatuan', 'databarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->where('databarang.status', '1')
            ->where(fn (Builder $query): Builder => $query
                ->when($golongan === 'narkotika', fn (Builder $q): Builder => $q->where('databarang.kode_golongan', 'G07'))
                ->when($golongan === 'psikotropika', fn (Builder $q): Builder => $q->where('databarang.kode_golongan', 'G01'))
            )
            ->withCasts([
                'stok_awal'             => 'float',
                'stok_awal_terakhir'    => 'float',
                'tf_masuk'              => 'float',
                'penerimaan_obat'       => 'float',
                'piutang_masuk'         => 'float',
                'hibah_obat'            => 'float',
                'retur_pasien'          => 'float',
                'hapus_beriobat'        => 'float',
                'pemberian_obat'        => 'float',
                'penjualan_obat'        => 'float',
                'piutang_keluar'        => 'float',
                'tf_keluar'             => 'float',
                'retur_supplier'        => 'float',
            ]);
    }

    public function scopePemakaianObatNAPZAAgregat(Builder $query, string $tglAwal = '', string $tglAkhir = '', string $golongan = 'narkotika'): Builder
    {
        $detail = static::query()->pemakaianObatNAPZA($tglAwal, $tglAkhir, $golongan);

        $this->searchColumns = [];

        $this->addSearchConditions([
            'detail.kode_brng',
            'detail.nama_brng',
            'detail.nama',
            'detail.satuan',
        ]);

        $sqlSelect = <<<'SQL'
            detail.kode_brng,
            detail.nama_brng,
            detail.kode_golongan,
            detail.nama,
            detail.satuan,            
            sum(detail.stok_awal) stok_awal,
            sum(detail.tf_masuk) tf_masuk,
            sum(detail.penerimaan_obat) penerimaan_obat,
            sum(detail.piutang_masuk) piutang_masuk,
            sum(detail.hibah_obat) hibah_obat,
            sum(detail.retur_pasien) retur_pasien,
            sum(detail.hapus_beriobat) hapus_beriobat,
            sum(detail.pemberian_obat) pemberian_obat,
            sum(detail.penjualan_obat) penjualan_obat,
            sum(detail.piutang_keluar) piutang_keluar,
            sum(detail.tf_keluar) tf_keluar,
            sum(detail.retur_supplier) retur_supplier
        SQL;

        $this->addRawColumns([
            'stok_awal'             => DB::raw('sum(detail.stok_awal)'),
            'tf_masuk'              => DB::raw('sum(detail.tf_masuk)'),
            'penerimaan_obat'       => DB::raw('sum(detail.penerimaan_obat)'),
            'piutang_masuk'         => DB::raw('sum(detail.piutang_masuk)'),
            'hibah_obat'            => DB::raw('sum(detail.hibah_obat)'),
            'retur_pasien'          => DB::raw('sum(detail.retur_pasien)'),
            'hapus_beriobat'        => DB::raw('sum(detail.hapus_beriobat)'),
            'pemberian_obat'        => DB::raw('sum(detail.pemberian_obat)'),
            'penjualan_obat'        => DB::raw('sum(detail.penjualan_obat)'),
            'piutang_keluar'        => DB::raw('sum(detail.piutang_keluar)'),
            'tf_keluar'             => DB::raw('sum(detail.tf_keluar)'),
            'retur_supplier'        => DB::raw('sum(detail.retur_supplier)'),
        ]);

        return $query
            ->fromSub($detail, 'detail')
            ->selectRaw($sqlSelect)
            ->groupBy([
                'detail.kode_brng',
                'detail.nama_brng',
                'detail.kode_golongan',
                'detail.nama',
                'detail.satuan',
            ])
            ->withCasts([
                'stok_awal'             => 'float',
                'stok_awal_terakhir'    => 'float',
                'tf_masuk'              => 'float',
                'penerimaan_obat'       => 'float',
                'piutang_masuk'         => 'float',
                'hibah_obat'            => 'float',
                'retur_pasien'          => 'float',
                'hapus_beriobat'        => 'float',
                'pemberian_obat'        => 'float',
                'penjualan_obat'        => 'float',
                'piutang_keluar'        => 'float',
                'tf_keluar'             => 'float',
                'retur_supplier'        => 'float',
            ]);
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
