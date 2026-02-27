<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PenerimaanObatDetail extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = null;

    protected $table = 'detailpesan';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeHppPembelianTerakhir(Builder $query, $kodeBangsal = ''): Builder
    {
        $hargaKecil = 'detailpesan.h_pesan / NULLIF(detailpesan.jumlah2 / NULLIF(detailpesan.jumlah, 0), 0)';
        $setelahDiskon = "($hargaKecil * (1 - (detailpesan.dis / 100)))";
        $hpp = "round(($setelahDiskon * 1.11), 2)";

        $this->addSearchConditions([
            'x.no_faktur',
            'x.kode_brng',
            'x.nama_brng',
            'x.nm_bangsal',
        ]);

        return $query->fromSub(function ($sub) use ($hargaKecil, $hpp, $kodeBangsal) {

            $sub->selectRaw("
                    detailpesan.no_faktur,
                    pemesanan.tgl_pesan,
                    bangsal.nm_bangsal,
                    detailpesan.kode_brng,
                    databarang.nama_brng,
                    databarang.kode_satbesar,
                    databarang.isi,
                    databarang.kode_sat,
                    databarang.kapasitas,
                    gudangbarang.stok,
                    detailpesan.h_pesan,
                    detailpesan.dis,
                    round($hargaKecil, 2) as harga_satuan,
                    $hpp as hpp
                ")
                ->from('detailpesan')
                ->join('pemesanan', 'detailpesan.no_faktur', '=', 'pemesanan.no_faktur')
                ->join('databarang', 'detailpesan.kode_brng', '=', 'databarang.kode_brng')
                ->join('gudangbarang', 'detailpesan.kode_brng', '=', 'gudangbarang.kode_brng')
                ->join('bangsal', 'gudangbarang.kd_bangsal', '=', 'bangsal.kd_bangsal')
                ->joinSub(
                    function ($query) {
                        $query->selectRaw('kode_brng, tgl_terakhir, h_pesan_terakhir')
                            ->fromSub(function ($inner) {
                                $inner->selectRaw('
                                        d2.kode_brng,
                                        p2.tgl_pesan AS tgl_terakhir,
                                        d2.h_pesan AS h_pesan_terakhir,
                                        ROW_NUMBER() OVER (
                                            PARTITION BY d2.kode_brng
                                            ORDER BY p2.tgl_pesan DESC, d2.h_pesan DESC
                                        ) AS rn
                                    ')
                                    ->from('detailpesan as d2')
                                    ->join('pemesanan as p2', 'd2.no_faktur', '=', 'p2.no_faktur');
                            }, 'ranked')
                            ->where('rn', 1);
                    },
                    'last_order',
                    function ($join) {
                        $join->on('detailpesan.kode_brng', '=', 'last_order.kode_brng')
                            ->on('pemesanan.tgl_pesan', '=', 'last_order.tgl_terakhir')
                            ->on('detailpesan.h_pesan', '=', 'last_order.h_pesan_terakhir');
                    }
                )->when($kodeBangsal !== '-', function ($q) use ($kodeBangsal) {
                    $q->where('bangsal.kd_bangsal', $kodeBangsal);
                });
        }, 'x')
            ->selectRaw('
            x.*,
            ROUND(x.hpp * x.stok, 2) as total_nilai_stok
        ')
            ->orderBy('x.kode_brng', 'asc');
    }
}
