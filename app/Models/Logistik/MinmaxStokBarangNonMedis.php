<?php

namespace App\Models\Logistik;

use App\Support\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MinmaxStokBarangNonMedis extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql_smc';

    protected $primaryKey = 'kode_brng';

    protected $keyType = 'string';

    protected $table = 'ipsrs_minmax_stok_barang';

    protected $fillable = [
        'kode_brng',
        'stok_min',
        'stok_max',
        'kode_suplier',
    ];
}
