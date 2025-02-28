<?php

namespace App\Models\Logistik;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PemesananBarangNonMedis extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_faktur';

    protected $keyType = 'string';

    protected $table = 'ipsrspemesanan';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeHutangAging(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
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

        $this->addSearchConditions([
            'ipsrs_detail_titip_faktur.no_tagihan',
            'ipsrspemesanan.no_order',
            'ipsrspemesanan.no_faktur',
            'ipsrssuplier.nama_suplier',
            'ipsrspemesanan.status',
            "ifnull(bayar_pemesanan_non_medis.nama_bayar, '-')",
            "ifnull(bayar_pemesanan_non_medis.keterangan, '-')",
        ]);

        $this->addRawColumns([
            'tgl_tagihan'   => 'ipsrs_titip_faktur.tanggal',
            'tgl_terima'    => 'ipsrspemesanan.tgl_pesan',
            'tagihan'       => DB::raw('round(ipsrspemesanan.tagihan, 2)'),
            'dibayar'       => DB::raw('round(bayar_pemesanan_non_medis.besar_bayar, 2)'),
            'sisa'          => DB::raw('round(ipsrspemesanan.tagihan - ifnull(bayar_pemesanan_non_medis.besar_bayar, 0), 2)'),
            'periode_0_30'  => DB::raw("datediff('{$this->tglAkhir}', ipsrs_titip_faktur.tanggal) <= 30"),
            'periode_31_60' => DB::raw("datediff('{$this->tglAkhir}', ipsrs_titip_faktur.tanggal) between 31 and 60"),
            'periode_61_90' => DB::raw("datediff('{$this->tglAkhir}', ipsrs_titip_faktur.tanggal) between 61 and 90"),
            'periode_90_up' => DB::raw("datediff('{$this->tglAkhir}', ipsrs_titip_faktur.tanggal) > 90"),
        ]);

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
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            case
                when datediff(?, ipsrs_titip_faktur.tanggal) <= 30 then 'periode_0_30'
                when datediff(?, ipsrs_titip_faktur.tanggal) between 31 and 60 then 'periode_31_60'
                when datediff(?, ipsrs_titip_faktur.tanggal) between 61 and 90 then 'periode_61_90'
                when datediff(?, ipsrs_titip_faktur.tanggal) > 90 then 'periode_90_up'
            end periode,
            round(sum(ipsrspemesanan.tagihan), 2) total_tagihan,
            round(sum(bayar_pemesanan_non_medis.besar_bayar), 2) total_dibayar,
            round(sum(ipsrspemesanan.tagihan - ifnull(bayar_pemesanan_non_medis.besar_bayar, 0)), 2) sisa_tagihan
            SQL;

        $sqlGroupBy = <<<'SQL'
            datediff(?, ipsrs_titip_faktur.tanggal) <= 30,
            datediff(?, ipsrs_titip_faktur.tanggal) between 31 and 60,
            datediff(?, ipsrs_titip_faktur.tanggal) between 61 and 90,
            datediff(?, ipsrs_titip_faktur.tanggal) > 90
            SQL;

        $this->addSearchConditions([
            'ipsrs_detail_titip_faktur.no_tagihan',
            'ipsrspemesanan.no_order',
            'ipsrspemesanan.no_faktur',
            'ipsrssuplier.nama_suplier',
            'ipsrspemesanan.status',
            'bayar_pemesanan_non_medis.nama_bayar',
            'bayar_pemesanan_non_medis.keterangan',
        ]);

        return $query
            ->selectRaw($sqlSelect, [$tglAkhir, $tglAkhir, $tglAkhir, $tglAkhir])
            ->leftJoin('bayar_pemesanan_non_medis', 'ipsrspemesanan.no_faktur', '=', 'bayar_pemesanan_non_medis.no_faktur')
            ->leftJoin('ipsrssuplier', 'ipsrspemesanan.kode_suplier', '=', 'ipsrssuplier.kode_suplier')
            ->leftJoin('ipsrs_detail_titip_faktur', 'ipsrspemesanan.no_faktur', '=', 'ipsrs_detail_titip_faktur.no_faktur')
            ->leftJoin('ipsrs_titip_faktur', 'ipsrs_detail_titip_faktur.no_tagihan', '=', 'ipsrs_titip_faktur.no_tagihan')
            ->whereBetween('ipsrs_titip_faktur.tanggal', [$tglAwal, $tglAkhir])
            ->whereRaw('round(ipsrspemesanan.tagihan, 2) != ifnull(round(bayar_pemesanan_non_medis.besar_bayar, 2), 0)')
            ->groupByRaw($sqlGroupBy, [$tglAkhir, $tglAkhir, $tglAkhir, $tglAkhir]);
    }
}
