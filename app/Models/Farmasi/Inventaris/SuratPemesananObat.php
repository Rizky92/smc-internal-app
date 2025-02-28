<?php

namespace App\Models\Farmasi\Inventaris;

use App\Database\Eloquent\Model;
use App\Models\Farmasi\PenerimaanObat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class SuratPemesananObat extends Model
{
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
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            surat_pemesanan_medis.no_pemesanan,
            databarang.nama_brng,
            datasuplier.nama_suplier suplier_pesan,
            ifnull(pemesanan_datang.nama_suplier, '-') suplier_datang,
            detail_surat_pemesanan_medis.jumlah2 jumlah_pesan,
            ifnull(pemesanan_datang.jumlah, 0) jumlah_datang,
            (detail_surat_pemesanan_medis.jumlah2 - ifnull(pemesanan_datang.jumlah, 0)) selisih,
            if(pemesanan_datang.jumlah is null, 'Barang belum datang', null) keterangan
            SQL;

        $queryPOYangDatang = PenerimaanObat::query()
            ->selectRaw('pemesanan.no_order, pemesanan.tgl_pesan, detailpesan.kode_brng, detailpesan.jumlah2 as jumlah, pemesanan.kode_suplier, datasuplier.nama_suplier')
            ->join('datasuplier', 'pemesanan.kode_suplier', '=', 'datasuplier.kode_suplier')
            ->join('detailpesan', 'pemesanan.no_faktur', '=', 'detailpesan.no_faktur')
            ->join('databarang', 'detailpesan.kode_brng', '=', 'databarang.kode_brng');

        $this->addSearchConditions([
            'surat_pemesanan_medis.no_pemesanan',
            'databarang.nama_brng',
            'datasuplier.nama_suplier',
            "ifnull(pemesanan_datang.nama_suplier, '-')",
        ]);

        $this->addRawColumns([
            'suplier_pesan'  => 'datasuplier.nama_suplier',
            'suplier_datang' => DB::raw("ifnull(pemesanan_datang.nama_suplier, '-')"),
            'jumlah_pesan'   => 'detail_surat_pemesanan_medis.jumlah2',
            'jumlah_datang'  => DB::raw('ifnull(pemesanan_datang.jumlah, 0)'),
            'selisih'        => DB::raw("ifnull((detail_surat_pemesanan_medis.jumlah2 - pemesanan_datang.jumlah), 'Barang belum datang')"),
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts([
                'jumlah_pesan'  => 'float',
                'jumlah_datang' => 'float',
                'selisih'       => 'float',
            ])
            ->join('datasuplier', 'surat_pemesanan_medis.kode_suplier', '=', 'datasuplier.kode_suplier')
            ->join('detail_surat_pemesanan_medis', 'surat_pemesanan_medis.no_pemesanan', '=', 'detail_surat_pemesanan_medis.no_pemesanan')
            ->join('databarang', 'detail_surat_pemesanan_medis.kode_brng', '=', 'databarang.kode_brng')
            ->leftJoinSub($queryPOYangDatang, 'pemesanan_datang', fn (JoinClause $join) => $join
                ->on('surat_pemesanan_medis.no_pemesanan', '=', 'pemesanan_datang.no_order')
                ->on('detail_surat_pemesanan_medis.kode_brng', '=', 'pemesanan_datang.kode_brng'))
            ->whereBetween('surat_pemesanan_medis.tanggal', [$tglAwal, $tglAkhir])
            ->when($hanyaTampilkanYangBerbeda, fn (Builder $query) => $query
                ->whereRaw('(detail_surat_pemesanan_medis.jumlah2 - ifnull(pemesanan_datang.jumlah, 0)) != 0'));
    }
}
