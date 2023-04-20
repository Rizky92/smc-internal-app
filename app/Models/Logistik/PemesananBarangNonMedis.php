<?php

namespace App\Models\Logistik;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PemesananBarangNonMedis extends Model
{
    protected $connection = 'mysql_sik';
    
    protected $primaryKey = 'no_faktur';

    protected $keyType = 'string';

    protected $table = 'ipsrspemesanan';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeHutangAgingNonMedis(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
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
}
