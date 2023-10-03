<?php

namespace App\Models\Logistik;

use App\Database\Eloquent\Concerns\Searchable;
use App\Database\Eloquent\Concerns\Sortable;
use Illuminate\Database\Eloquent\Builder;
use App\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PemesananBarangNonMedis extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_faktur';

    protected $keyType = 'string';

    protected $table = 'ipsrspemesanan';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeHutangAging(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
            ipsrs_detail_titip_faktur.no_tagihan,
            ipsrspemesanan.no_order,
            ipsrspemesanan.no_faktur,
            ipsrssuplier.nama_suplier,
            ipsrs_titip_faktur.tanggal tgl_tagihan,
            ipsrspemesanan.tgl_tempo,
            ipsrspemesanan.tgl_pesan tgl_terima,
            bayar_pemesanan_non_medis.tgl_bayar,
            round(ipsrspemesanan.tagihan, 2) tagihan,
            ipsrspemesanan.status,
            round(bayar_pemesanan_non_medis.besar_bayar, 2) dibayar,
            round(ipsrspemesanan.tagihan - ifnull(bayar_pemesanan_non_medis.besar_bayar, 0), 2) sisa,
            bayar_pemesanan_non_medis.nama_bayar,
            bayar_pemesanan_non_medis.keterangan,
            datediff('{$tglAkhir}', ipsrs_titip_faktur.tanggal) umur_hari
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('bayar_pemesanan_non_medis', 'ipsrspemesanan.no_faktur', '=', 'bayar_pemesanan_non_medis.no_faktur')
            ->leftJoin('ipsrssuplier', 'ipsrspemesanan.kode_suplier', '=', 'ipsrssuplier.kode_suplier')
            ->leftJoin('ipsrs_detail_titip_faktur', 'ipsrspemesanan.no_faktur', '=', 'ipsrs_detail_titip_faktur.no_faktur')
            ->leftJoin('ipsrs_titip_faktur', 'ipsrs_detail_titip_faktur.no_tagihan', '=', 'ipsrs_titip_faktur.no_tagihan')
            ->whereBetween('ipsrs_titip_faktur.tanggal', [$tglAwal, $tglAkhir])
            ->whereRaw('round(ipsrspemesanan.tagihan, 2) != ifnull(round(bayar_pemesanan_non_medis.besar_bayar, 2), 0)')
            ->orderBy(DB::raw("datediff('{$tglAkhir}', ipsrs_titip_faktur.tanggal)"), 'desc');
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
                when datediff('{$tglAkhir}', ipsrs_titip_faktur.tanggal) <= 30 then 'periode_0_30'
                when datediff('{$tglAkhir}', ipsrs_titip_faktur.tanggal) between 31 and 60 then 'periode_31_60'
                when datediff('{$tglAkhir}', ipsrs_titip_faktur.tanggal) between 61 and 90 then 'periode_61_90'
                when datediff('{$tglAkhir}', ipsrs_titip_faktur.tanggal) > 90 then 'periode_90_up'
            end periode,
            round(sum(ipsrspemesanan.tagihan), 2) total_tagihan,
            round(sum(bayar_pemesanan_non_medis.besar_bayar), 2) total_dibayar,
            round(sum(ipsrspemesanan.tagihan - ifnull(bayar_pemesanan_non_medis.besar_bayar, 0)), 2) sisa_tagihan
        SQL;

        $sqlGroupBy = <<<SQL
            datediff('{$tglAkhir}', ipsrs_titip_faktur.tanggal) <= 30,
            datediff('{$tglAkhir}', ipsrs_titip_faktur.tanggal) between 31 and 60,
            datediff('{$tglAkhir}', ipsrs_titip_faktur.tanggal) between 61 and 90,
            datediff('{$tglAkhir}', ipsrs_titip_faktur.tanggal) > 90
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('bayar_pemesanan_non_medis', 'ipsrspemesanan.no_faktur', '=', 'bayar_pemesanan_non_medis.no_faktur')
            ->leftJoin('ipsrssuplier', 'ipsrspemesanan.kode_suplier', '=', 'ipsrssuplier.kode_suplier')
            ->leftJoin('ipsrs_detail_titip_faktur', 'ipsrspemesanan.no_faktur', '=', 'ipsrs_detail_titip_faktur.no_faktur')
            ->leftJoin('ipsrs_titip_faktur', 'ipsrs_detail_titip_faktur.no_tagihan', '=', 'ipsrs_titip_faktur.no_tagihan')
            ->whereBetween('ipsrs_titip_faktur.tanggal', [$tglAwal, $tglAkhir])
            ->whereRaw('round(ipsrspemesanan.tagihan, 2) != ifnull(round(bayar_pemesanan_non_medis.besar_bayar, 2), 0)')
            ->groupByRaw($sqlGroupBy);
    }
}
