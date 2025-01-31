<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class DepositKembali extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'pengembalian_deposit';

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeItemFakturPajak(Builder $query): Builder
    {
        $sqlSelect = <<<'SQL'
            pengembalian_deposit.no_rawat,
            '080' as kode_transaksi,
            'B' as jenis_barang_jasa,
            '000000' as kode_barang_jasa,
            'Deposit Kembali' as nama_barang_jasa,
            'UM.0033' as nama_satuan_ukur,
            pengembalian_deposit.besar_pengembalian as harga_satuan,
            1 as jumlah_barang_jasa,
            0 as diskon_persen,
            0 as diskon_nominal,
            pengembalian_deposit.besar_pengembalian as dpp,
            12 as ppn_persen,
            0 as ppn_nominal,
            '' as kd_jenis_prw,
            'Deposit Kembali' as kategori,
            'Ranap' as status_lanjut,
            18 as urutan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->whereExists(fn ($q) => $q->from('regist_faktur')->whereColumn('pengembalian_deposit.no_rawat', 'regist_faktur.no_rawat'));
    }
}
