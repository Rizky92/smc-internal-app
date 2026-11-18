<?php

namespace App\Models\Dapur;

use App\Database\Eloquent\Model;

class SupplierDapur extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'dapursuplier';

    protected $primaryKey = 'kode_suplier';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;
}
