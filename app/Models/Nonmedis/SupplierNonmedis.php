<?php

namespace App\Models\Nonmedis;

use Illuminate\Database\Eloquent\Model;

class SupplierNonmedis extends Model
{
    protected $primaryKey = 'kode_suplier';

    protected $keyType = 'string';

    protected $table = 'ipsrssuplier';

    public $incrementing = false;

    public $timestamps = false;
}
