<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DataBarang extends Model
{
    protected $primaryKey = 'kode_brng';

    protected $keyType = 'string';

    protected $table = 'databarang';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeStokDarurat(Builder $query)
    {
        // SELECT
        //     databarang.kode_brng,
        //     databarang.nama_brng,
        //     kodesatuan.satuan,
        //     databarang.stokminimal,
        //     jenis.nama,
        //     stok_gudang.stok_saat_ini
        // FROM databarang
        // INNER JOIN kodesatuan ON databarang.kode_sat = kodesatuan.kode_sat
        // INNER JOIN jenis ON databarang.kdjns = jenis.kdjns
        // INNER JOIN (
        //     SELECT
        //         kode_brng,
        //         sum(stok) stok_saat_ini
        //     FROM gudangbarang
        //     INNER JOIN bangsal ON gudangbarang.kd_bangsal = bangsal.kd_bangsal
        //     WHERE bangsal.status='1'
        //     AND gudangbarang.no_batch = ''
        //     AND gudangbarang.no_faktur = ''
        //     GROUP BY kode_brng
        // ) stok_gudang ON databarang.kode_brng = stok_gudang.kode_brng
        // WHERE databarang.status = '1'
        // ORDER BY databarang.nama_brng;
        return $query
            ->selectRaw("
                databarang.kode_brng,
                nama_brng,
                kodesatuan.satuan,
                stokminimal,
                jenis.nama,
                stok_gudang.stok_saat_ini,
                (databarang.stokminimal - stok_gudang.stok_saat_ini) selisih_stok
            ")
            ->join('kodesatuan', 'databarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->join('jenis', 'databarang.kdjns', '=', 'jenis.kdjns')
            ->join(DB::raw("(
                SELECT
                    kode_brng,
                    SUM(stok) stok_saat_ini
                FROM gudangbarang
                INNER JOIN bangsal ON gudangbarang.kd_bangsal = bangsal.kd_bangsal
                WHERE bangsal.status='1'
                AND gudangbarang.no_batch = ''
                AND gudangbarang.no_faktur = ''
                GROUP BY kode_brng
            ) stok_gudang"), 'databarang.kode_brng', '=', 'stok_gudang.kode_brng')
            ->where('status', '1');
    }
}
