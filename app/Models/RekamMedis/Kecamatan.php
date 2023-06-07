<?php

namespace App\Models\RekamMedis;

use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_kec';

    protected $keyType = 'int';

    protected $table = 'kecamatan';

    public $incrementing = false;

    public $timestamps = false;
}
