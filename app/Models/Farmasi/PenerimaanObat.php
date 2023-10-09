<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;
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
        $sqlSelect = <<<SQL
            round(sum(detailpesan.total)) jumlah,
            month(pemesanan.tgl_pesan) bulan
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['jumlah' => 'float', 'bulan' => 'int'])
            ->join('detailpesan', 'pemesanan.no_faktur', '=', 'detailpesan.no_faktur')
            ->whereBetween('pemesanan.tgl_pesan', ["{$year}-01-01", "{$year}-12-31"])
            ->groupByRaw("month(pemesanan.tgl_pesan)");
    }

    public function scopeHutangAging(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
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
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
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

        $sqlGroupBy = <<<SQL
            datediff(?, titip_faktur.tanggal) <= 30,
            datediff(?, titip_faktur.tanggal) between 31 and 60,
            datediff(?, titip_faktur.tanggal) between 61 and 90,
            datediff(?, titip_faktur.tanggal) > 90
        SQL;

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
}
