<?php

namespace App\Models\Farmasi\Inventaris;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierObat extends Model
{
    use HasFactory;

    protected $primaryKey = 'kode_suplier';

    protected $keyType = 'string';

    protected $table = 'datasuplier';

    public $incrementing = false;

    public $timestamps = false;
}
