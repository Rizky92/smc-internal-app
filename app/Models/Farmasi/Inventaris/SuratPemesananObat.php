<?php

namespace App\Models\Farmasi\Inventaris;

use App\Database\Eloquent\Model;
use App\Models\Farmasi\PenerimaanObat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
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
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
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

    public function scopeRincianPerbandinganPemesananPO(
        Builder $query,
        string $kategori = 'obat', 
        string $tglAwal = '', 
        string $tglAkhir = ''
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $tglAwalBulanLalu = Carbon::parse($tglAwal)->subMonth()->startOfMonth()->format('Y-m-d');
        $tglAkhirBulanLalu = Carbon::parse($tglAwal)->subMonth()->endOfMonth()->format('Y-m-d');

        $sqlSelect = <<<'SQL'
            databarang.kode_brng,
            databarang.nama_brng,
            sum(detail_surat_pemesanan_medis.jumlah) as total_pemesanan,
            sum(detail_surat_pemesanan_medis.total) as total_harga,
            ifnull(sum(detail_surat_pemesanan_medis.total) / nullif(sum(detail_surat_pemesanan_medis.jumlah), 0), 0) as harga_satuan, 
            ifnull((select sum(dspm2.jumlah) from detail_surat_pemesanan_medis dspm2 join surat_pemesanan_medis spm2 on dspm2.no_pemesanan = spm2.no_pemesanan where dspm2.kode_brng = databarang.kode_brng and spm2.tanggal between ? and ? and spm2.status = 'Sudah Datang'), 0) as total_pemesanan_bulan_lalu, 
            ifnull((select sum(dspm2.total) from detail_surat_pemesanan_medis dspm2 join surat_pemesanan_medis spm2 on dspm2.no_pemesanan = spm2.no_pemesanan where dspm2.kode_brng = databarang.kode_brng and spm2.tanggal between ? and ? and spm2.status = 'Sudah Datang'), 0) as total_harga_bulan_lalu,
            (sum(detail_surat_pemesanan_medis.jumlah) - ifnull((select sum(dspm2.jumlah) from detail_surat_pemesanan_medis dspm2 join surat_pemesanan_medis spm2 on dspm2.no_pemesanan = spm2.no_pemesanan where dspm2.kode_brng = databarang.kode_brng and spm2.tanggal between ? and ? and spm2.status = 'Sudah Datang'), 0)) as selisih_pemesanan,
            (sum(detail_surat_pemesanan_medis.total) - ifnull((select sum(dspm2.total) from detail_surat_pemesanan_medis dspm2 join surat_pemesanan_medis spm2 on dspm2.no_pemesanan = spm2.no_pemesanan where dspm2.kode_brng = databarang.kode_brng and spm2.tanggal between ? and ? and spm2.status = 'Sudah Datang'), 0)) as selisih_harga
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
            ->join('detail_surat_pemesanan_medis', 'surat_pemesanan_medis.no_pemesanan', '=', 'detail_surat_pemesanan_medis.no_pemesanan')
            ->join('databarang', 'detail_surat_pemesanan_medis.kode_brng', '=', 'databarang.kode_brng')
            ->whereBetween('surat_pemesanan_medis.tanggal', [$tglAwal, $tglAkhir])
            ->where('surat_pemesanan_medis.status', '=', 'Sudah Datang')
            ->where(fn (Builder $query): Builder => $query
                ->when($kategori === 'obat', fn(Builder $q): Builder => $q->where('databarang.kode_kategori', 'like', '2.%'))
                ->when($kategori === 'alkes', fn(Builder $q): Builder => $q->where('databarang.kode_kategori', 'like', '3.%')))
            ->groupBy('databarang.kode_brng', 'databarang.nama_brng');
    }
}
