<?php

namespace App\Models\Logistik;

use Illuminate\Database\Eloquent\Model;

class JenisBarangNonMedis extends Model
{
    protected $primaryKey = 'kd_jenis';

    protected $keyType = 'string';

    protected $table = 'ipsrsjenisbarang';

    public $incrementing = false;

    public $timestamps = false;
}
