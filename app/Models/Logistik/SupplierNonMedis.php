<?php

namespace App\Models\Logistik;

use App\Support\Eloquent\Model;

class SupplierNonMedis extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kode_suplier';

    protected $keyType = 'string';

    protected $table = 'ipsrssuplier';

    public $incrementing = false;

    public $timestamps = false;
}
