<?php

namespace App\Models\Nonmedis;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MinmaxBarangNonmedis extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql_smc';

    protected $primaryKey = 'kode_brng';

    protected $keyType = 'string';

    protected $table = 'ipsrs_minmax_stok_barang';

    protected $fillable = [
        'stok_min',
        'stok_max',
        'kode_suplier',
    ];
}
