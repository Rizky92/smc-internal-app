<?php

namespace App\Models\Logistik;

use Illuminate\Database\Eloquent\Model;

class SupplierNonMedis extends Model
{
    protected $primaryKey = 'kode_suplier';

    protected $keyType = 'string';

    protected $table = 'ipsrssuplier';

    public $incrementing = false;

    public $timestamps = false;
}
