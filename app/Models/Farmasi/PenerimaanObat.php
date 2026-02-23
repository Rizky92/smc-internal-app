<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PenerimaanObat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_faktur';

    protected $keyType = 'string';

    protected $table = 'pemesanan';

    public $incrementing = false;

    public $timestamps = false;

    public function scopePembelianFarmasi(Builder $query, string $year = '2022'): Builder
    {
        $sqlSelect = <<<'SQL'
            round(sum(detailpesan.total)) jumlah,
            month(pemesanan.tgl_pesan) bulan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['jumlah' => 'float', 'bulan' => 'int'])
            ->join('detailpesan', 'pemesanan.no_faktur', '=', 'detailpesan.no_faktur')
            ->whereBetween('pemesanan.tgl_pesan', ["{$year}-01-01", "{$year}-12-31"])
            ->groupByRaw('month(pemesanan.tgl_pesan)');
    }

    public function scopeHutangAging(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            detail_titip_faktur.no_tagihan,
            pemesanan.no_order,
            pemesanan.no_faktur,
            datasuplier.nama_suplier,
            titip_faktur.tanggal tgl_tagihan,
            pemesanan.tgl_tempo,
            pemesanan.tgl_pesan tgl_terima,
            bayar_pemesanan.tgl_bayar,
            pemesanan.status,
            bayar_pemesanan.nama_bayar,
            round(pemesanan.tagihan, 2) tagihan,
            round(bayar_pemesanan.besar_bayar, 2) dibayar,
            round(pemesanan.tagihan - ifnull(bayar_pemesanan.besar_bayar, 0), 2) sisa,
            bayar_pemesanan.keterangan,
            datediff(?, titip_faktur.tanggal) umur_hari
            SQL;

        $this->addSearchConditions([
            'detail_titip_faktur.no_tagihan',
            'pemesanan.no_order',
            'pemesanan.no_faktur',
            'datasuplier.nama_suplier',
            'pemesanan.status',
            "ifnull(bayar_pemesanan.nama_bayar, '-')",
            "ifnull(bayar_pemesanan.keterangan, '-')",
        ]);

        $this->addRawColumns([
            'tgl_tagihan'   => 'titip_faktur.tanggal',
            'tgl_terima'    => 'pemesanan.tgl_pesan',
            'tagihan'       => DB::raw('round(pemesanan.tagihan, 2)'),
            'dibayar'       => DB::raw('round(bayar_pemesanan.besar_bayar, 2)'),
            'sisa'          => DB::raw('round(pemesanan.tagihan - ifnull(bayar_pemesanan.besar_bayar, 0), 2)'),
            'periode_0_30'  => DB::raw("datediff('{$this->tglAkhir}', titip_faktur.tanggal) <= 30"),
            'periode_31_60' => DB::raw("datediff('{$this->tglAkhir}', titip_faktur.tanggal) between 31 and 60"),
            'periode_61_90' => DB::raw("datediff('{$this->tglAkhir}', titip_faktur.tanggal) between 61 and 90"),
            'periode_90_up' => DB::raw("datediff('{$this->tglAkhir}', titip_faktur.tanggal) > 90"),
        ]);

        return $query
            ->selectRaw($sqlSelect, [$tglAkhir])
            ->withCasts([
                'nama_bayar' => 'float',
                'tagihan'    => 'float',
                'dibayar'    => 'float',
                'sisa'       => 'float',
                'umur_hari'  => 'int',
            ])
            ->leftJoin('bayar_pemesanan', 'pemesanan.no_faktur', '=', 'bayar_pemesanan.no_faktur')
            ->leftJoin('datasuplier', 'pemesanan.kode_suplier', '=', 'datasuplier.kode_suplier')
            ->leftJoin('detail_titip_faktur', 'pemesanan.no_faktur', '=', 'detail_titip_faktur.no_faktur')
            ->leftJoin('titip_faktur', 'detail_titip_faktur.no_tagihan', '=', 'titip_faktur.no_tagihan')
            ->whereBetween('titip_faktur.tanggal', [$tglAwal, $tglAkhir])
            ->whereRaw('round(pemesanan.tagihan, 2) != ifnull(round(bayar_pemesanan.besar_bayar, 2), 0)')
            ->orderBy(DB::raw("datediff('{$tglAkhir}', titip_faktur.tanggal)"), 'desc');
    }

    public function scopeTotalHutangAging(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            case
                when datediff(?, titip_faktur.tanggal) <= 30 then 'periode_0_30'
                when datediff(?, titip_faktur.tanggal) between 31 and 60 then 'periode_31_60'
                when datediff(?, titip_faktur.tanggal) between 61 and 90 then 'periode_61_90'
                when datediff(?, titip_faktur.tanggal) > 90 then 'periode_90_up'
            end periode,
            round(sum(pemesanan.tagihan), 2) total_tagihan,
            round(sum(bayar_pemesanan.besar_bayar), 2) total_dibayar,
            round(sum(pemesanan.tagihan - ifnull(bayar_pemesanan.besar_bayar, 0)), 2) sisa_tagihan
            SQL;

        $sqlGroupBy = <<<'SQL'
            datediff(?, titip_faktur.tanggal) <= 30,
            datediff(?, titip_faktur.tanggal) between 31 and 60,
            datediff(?, titip_faktur.tanggal) between 61 and 90,
            datediff(?, titip_faktur.tanggal) > 90
            SQL;

        $this->addSearchConditions([
            'detail_titip_faktur.no_tagihan',
            'pemesanan.no_order',
            'pemesanan.no_faktur',
            'datasuplier.nama_suplier',
            'pemesanan.status',
            'bayar_pemesanan.nama_bayar',
            'bayar_pemesanan.keterangan',
        ]);

        return $query
            ->selectRaw($sqlSelect, [$tglAkhir, $tglAkhir, $tglAkhir, $tglAkhir])
            ->withCasts(['total_tagihan' => 'float', 'total_dibayar' => 'float', 'sisa_tagihan' => 'float'])
            ->leftJoin('bayar_pemesanan', 'pemesanan.no_faktur', '=', 'bayar_pemesanan.no_faktur')
            ->leftJoin('datasuplier', 'pemesanan.kode_suplier', '=', 'datasuplier.kode_suplier')
            ->leftJoin('detail_titip_faktur', 'pemesanan.no_faktur', '=', 'detail_titip_faktur.no_faktur')
            ->leftJoin('titip_faktur', 'detail_titip_faktur.no_tagihan', '=', 'titip_faktur.no_tagihan')
            ->whereBetween('titip_faktur.tanggal', [$tglAwal, $tglAkhir])
            ->whereRaw('round(pemesanan.tagihan, 2) != ifnull(round(bayar_pemesanan.besar_bayar, 2), 0)')
            ->groupByRaw($sqlGroupBy, [$tglAkhir, $tglAkhir, $tglAkhir, $tglAkhir]);
    }

    public static function totalPembelianDariFarmasi(string $year = '2022'): array
    {
        $data = static::pembelianFarmasi($year)->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }

    public function scopeRincianPerbandinganPemesananPO(
        Builder $query,
        string $kategori = 'obat',
        string $tglAwal = '',
        string $tglAkhir = ''
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $tglAwalBulanLalu = Carbon::parse($tglAwal)->subMonth()->startOfMonth()->toDateString();
        $tglAkhirBulanLalu = Carbon::parse($tglAwal)->subMonth()->endOfMonth()->toDateString();

        $sqlSelect = <<<'SQL'
            detailpesan.kode_brng,
            databarang.nama_brng,
            databarang.dasar as harga_satuan,
            ifnull(sum(detailpesan.jumlah2), 0) as total_pemesanan,
            ifnull(sum(detailpesan.total), 0) as total_harga, 
            ifnull((select sum(dp2.jumlah2) from detailpesan dp2 join pemesanan p2 on dp2.no_faktur = p2.no_faktur where dp2.kode_brng = databarang.kode_brng and p2.tgl_pesan between ? and ?), 0) as total_pemesanan_bulan_lalu,
            ifnull((select sum(dp2.total) from detailpesan dp2 join pemesanan p2 on dp2.no_faktur = p2.no_faktur where dp2.kode_brng = databarang.kode_brng and p2.tgl_pesan between ? and ?), 0) as total_harga_bulan_lalu,
            ifnull(sum(detailpesan.jumlah2), 0) - ifnull((select sum(dp2.jumlah2) from detailpesan dp2 join pemesanan p2 on dp2.no_faktur = p2.no_faktur where dp2.kode_brng = databarang.kode_brng and p2.tgl_pesan between ? and ?), 0) as selisih_pemesanan,
            ifnull(sum(detailpesan.total), 0) - ifnull((select sum(dp2.total) from detailpesan dp2 join pemesanan p2 on dp2.no_faktur = p2.no_faktur where dp2.kode_brng = databarang.kode_brng and p2.tgl_pesan between ? and ?), 0) as selisih_harga
            SQL;

        $this->addSearchConditions([
            'databarang.kode_brng',
            'databarang.nama_brng',
        ]);

        return $query
            ->selectRaw($sqlSelect, [
                $tglAwalBulanLalu, $tglAkhirBulanLalu,
                $tglAwalBulanLalu, $tglAkhirBulanLalu,
                $tglAwalBulanLalu, $tglAkhirBulanLalu,
                $tglAwalBulanLalu, $tglAkhirBulanLalu,
            ])
            ->withCasts([
                'harga_satuan'                  => 'float',
                'total_pemesanan'               => 'float',
                'total_tagihan'                 => 'float',
                'total_pemesanan_bulan_lalu'    => 'float',
                'total_harga_bulan_lalu'        => 'float',
                'selisih_pemesanan'             => 'float',
                'selisih_harga'                 => 'float',
            ])
            ->join('detailpesan', 'pemesanan.no_faktur', '=', 'detailpesan.no_faktur')
            ->join('databarang', 'detailpesan.kode_brng', '=', 'databarang.kode_brng')
            ->whereBetween('pemesanan.tgl_pesan', [$tglAwal, $tglAkhir])
            ->where(fn (Builder $query): Builder => $query
                ->when($kategori === 'obat', fn (Builder $q): Builder => $q->where('databarang.kode_kategori', 'like', '2.%'))
                ->when($kategori === 'alkes', fn (Builder $q): Builder => $q->where('databarang.kode_kategori', 'like', '3.%')))
            ->groupBy('detailpesan.kode_brng');
    }
}
