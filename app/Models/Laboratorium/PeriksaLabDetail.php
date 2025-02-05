<?php

namespace App\Models\Laboratorium;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class PeriksaLabDetail extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'detail_periksa_lab';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeItemFakturPajak(Builder $query): Builder
    {
        $sqlSelect = <<<'SQL'
            detail_periksa_lab.no_rawat,
            '080' as kode_transaksi,
            'B' as jenis_barang_jasa,
            '250100' as kode_barang_jasa,
            template_laboratorium.Pemeriksaan as nama_barang_jasa,
            '' as nama_satuan_ukur,
            detail_periksa_lab.biaya_item as harga_satuan,
            count(*) as jumlah_barang_jasa,
            0 as diskon_persen,
            0 as diskon_nominal,
            (detail_periksa_lab.biaya_item * count(*)) as dpp,
            12 as ppn_persen,
            0 as ppn_nominal,
            concat_ws('-', detail_periksa_lab.kd_jenis_prw, detail_periksa_lab.id_template) as kd_jenis_prw,
            'Laborat Detail' as kategori,
            10 as urutan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('template_laboratorium', 'detail_periksa_lab.id_template', '=', 'template_laboratorium.id_template')
            ->join('periksa_lab', fn (JoinClause $join) => $join
                ->on('detail_periksa_lab.no_rawat', '=', 'periksa_lab.no_rawat')
                ->on('detail_periksa_lab.kd_jenis_prw', '=', 'periksa_lab.kd_jenis_prw')
                ->on('detail_periksa_lab.tgl_periksa', '=', 'periksa_lab.tgl_periksa')
                ->on('detail_periksa_lab.jam', '=', 'periksa_lab.jam')
                ->on('periksa_lab.kategori', '=', DB::raw('\'PK\'')))
            ->whereExists(fn ($q) => $q->from('regist_faktur')->whereColumn('regist_faktur.no_rawat', 'detail_periksa_lab.no_rawat'))
            ->groupBy(['detail_periksa_lab.no_rawat', 'detail_periksa_lab.id_template', 'template_laboratorium.Pemeriksaan', 'detail_periksa_lab.biaya_item']);
    }
}
