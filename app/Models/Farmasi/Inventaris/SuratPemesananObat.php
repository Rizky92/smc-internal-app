<?php

namespace App\Models\Farmasi\Inventaris;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class SuratPemesananObat extends Model
{
    use Searchable, Sortable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_pemesanan';

    protected $keyType = 'string';

    protected $table = 'surat_pemesanan_medis';

    public $incrementing = false;

    public $timestamps = false;

    public function scopePerbandinganPemesananObatPO(
        Builder $query,
        string $tglAwal = '',
        string $tglAkhir = '',
        bool $hanyaTampilkanYangBerbeda = false
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
            surat_pemesanan_medis.no_pemesanan,
            databarang.nama_brng,
            datasuplier.nama_suplier suplier_pesan,
            ifnull(pemesanan_datang.nama_suplier, '-') suplier_datang,
            detail_surat_pemesanan_medis.jumlah2 jumlah_pesan,
            ifnull(pemesanan_datang.jumlah, 0) jumlah_datang,
            ifnull((detail_surat_pemesanan_medis.jumlah2 - pemesanan_datang.jumlah), 'Barang belum datang') selisih
        SQL;

        $pemesananYangDatang = DB::raw("(
            select
                pemesanan.no_order,
                pemesanan.tgl_pesan,
                detailpesan.kode_brng,
                detailpesan.jumlah2 as jumlah,
                pemesanan.kode_suplier,
                datasuplier.nama_suplier
            from pemesanan
            inner join datasuplier on pemesanan.kode_suplier = datasuplier.kode_suplier
            inner join detailpesan on pemesanan.no_faktur = detailpesan.no_faktur
            inner join databarang on detailpesan.kode_brng = databarang.kode_brng
        ) pemesanan_datang");

        $jumlahObatYangBerbeda = DB::raw('(detail_surat_pemesanan_medis.jumlah2 - ifnull(pemesanan_datang.jumlah, 0))');

        return $query
            ->selectRaw($sqlSelect)
            ->join('datasuplier', 'surat_pemesanan_medis.kode_suplier', '=', 'datasuplier.kode_suplier')
            ->join('detail_surat_pemesanan_medis', 'surat_pemesanan_medis.no_pemesanan', '=', 'detail_surat_pemesanan_medis.no_pemesanan')
            ->join('databarang', 'detail_surat_pemesanan_medis.kode_brng', '=', 'databarang.kode_brng')
            ->leftJoin($pemesananYangDatang, fn (JoinClause $join) => $join
                ->on('surat_pemesanan_medis.no_pemesanan', '=', 'pemesanan_datang.no_order')
                ->on('detail_surat_pemesanan_medis.kode_brng', '=', 'pemesanan_datang.kode_brng'))
            ->whereBetween('surat_pemesanan_medis.tanggal', [$tglAwal, $tglAkhir])
            ->when($hanyaTampilkanYangBerbeda, fn (Builder $query) => $query->where($jumlahObatYangBerbeda, '!=', 0));
    }
}
