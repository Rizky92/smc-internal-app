<?php

namespace App\Models\RekamMedis;

use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_prop';

    protected $table = 'propinsi';

    public $timestamps = false;
}
