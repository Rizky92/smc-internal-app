<?php

namespace App\Models\Dapur;

use App\Database\Eloquent\Model;

class BarangDapur extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'dapur_barang';

    protected $primaryKey = 'kode_brng';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;
}
