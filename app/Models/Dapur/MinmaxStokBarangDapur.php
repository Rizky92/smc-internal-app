<?php

namespace App\Models\Dapur;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MinmaxStokBarangDapur extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql_smc';

    protected $table = 'minmax_stok_dapur';

    protected $primaryKey = 'kode_brng';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = true;

    protected $fillable = [
        'kode_brng',
        'stok_min',
        'stok_max',
        'kode_suplier',
    ];
}
