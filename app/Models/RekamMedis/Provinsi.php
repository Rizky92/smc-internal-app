<?php

namespace App\Models\RekamMedis;

use App\Database\Eloquent\Model;

class Provinsi extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_prop';

    protected $keyType = 'int';

    protected $table = 'propinsi';

    public $incrementing = false;

    public $timestamps = false;
}
