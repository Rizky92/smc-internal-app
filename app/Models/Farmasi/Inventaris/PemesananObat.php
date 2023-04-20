<?php

namespace App\Models\Farmasi\Inventaris;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PemesananObat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_faktur';

    protected $keyType = 'string';

    protected $table = 'pemesanan';

    public $incrementing = false;

    public $timestamps = false;

    public function scopePembelianFarmasi(Builder $query, string $year = '2022'): Builder
    {
        return $query->selectRaw("
            round(sum(detailpesan.total)) jumlah,
            month(pemesanan.tgl_pesan) bulan
        ")
            ->join('detailpesan', 'pemesanan.no_faktur', '=', 'detailpesan.no_faktur')
            ->whereBetween('pemesanan.tgl_pesan', ["{$year}-01-01", "{$year}-12-31"])
            ->groupByRaw("month(pemesanan.tgl_pesan)");
    }

    public function scopeHutangAgingMedis(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
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
            round(pemesanan.tagihan, 2) tagihan,
            pemesanan.status,
            round(bayar_pemesanan.besar_bayar, 2) dibayar,
            round(pemesanan.tagihan - ifnull(bayar_pemesanan.besar_bayar, 0), 2) sisa,
            bayar_pemesanan.nama_bayar,
            bayar_pemesanan.keterangan,
            datediff('2023-04-30', titip_faktur.tanggal) umur_hari
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('bayar_pemesanan', 'pemesanan.no_faktur', '=', 'bayar_pemesanan.no_faktur')
            ->leftJoin('datasuplier', 'pemesanan.kode_suplier', '=', 'datasuplier.kode_suplier')
            ->leftJoin('detail_titip_faktur', 'pemesanan.no_faktur', '=', 'detail_titip_faktur.no_faktur')
            ->leftJoin('titip_faktur', 'detail_titip_faktur.no_tagihan', '=', 'titip_faktur.no_tagihan')
            ->whereBetween('titip_faktur.tanggal', [$tglAwal, $tglAkhir])
            ->whereRaw('round(pemesanan.tagihan, 2) != ifnull(round(bayar_pemesanan.besar_bayar, 2), 0)')
            ->orderBy(DB::raw("datediff('{$tglAkhir}', titip_faktur.tanggal)"), 'desc');
    }

    public static function totalPembelianDariFarmasi(string $year = '2022'): array
    {
        $data = static::pembelianFarmasi($year)->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }
}
