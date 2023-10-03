<?php

namespace App\Models\RekamMedis;

use App\Database\Eloquent\Model;

class Kabupaten extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_kab';

    protected $keyType = 'int';

    protected $table = 'kabupaten';

    public $incrementing = false;

    public $timestamps = false;
}
