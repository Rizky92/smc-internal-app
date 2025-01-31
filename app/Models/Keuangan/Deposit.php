<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Deposit extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'deposit';

    protected $primaryKey = 'no_deposit';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeItemFakturPajak(Builder $query): Builder
    {
        $sqlSelect = <<<'SQL'
            deposit.no_rawat,
            '080' as kode_transaksi,
            'B' as jenis_barang_jasa,
            '000000' as kode_barang_jasa,
            deposit.keterangan as nama_barang_jasa,
            'UM.0033' as nama_satuan_ukur,
            deposit.besar_deposit as harga_satuan,
            1 as jumlah_barang_jasa,
            0 as diskon_persen,
            0 as diskon_nominal,
            deposit.besar_deposit as dpp,
            12 as ppn_persen,
            0 as ppn_nominal,
            deposit.no_deposit as kd_jenis_prw,
            'Deposit' as kategori,
            'Ranap' as status_lanjut,
            17 as urutan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->whereExists(fn ($q) => $q->from('regist_faktur')->whereColumn('deposit.no_rawat', 'regist_faktur.no_rawat'));
    }
}
