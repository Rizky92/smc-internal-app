<?php

namespace App\Models\RekamMedis;

use Illuminate\Database\Eloquent\Model;

class Kabupaten extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_kab';

    protected $table = 'kabupaten';

    public $timestamps = false;
}
