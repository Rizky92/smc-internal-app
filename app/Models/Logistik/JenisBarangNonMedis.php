<?php

namespace App\Models\Logistik;

use App\Support\Eloquent\Model;

class JenisBarangNonMedis extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_jenis';

    protected $keyType = 'string';

    protected $table = 'ipsrsjenisbarang';

    public $incrementing = false;

    public $timestamps = false;
}
