<?php

namespace App\Models\RekamMedis;

use Illuminate\Database\Eloquent\Model;

class Kelurahan extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_kel';

    protected $table = 'kelurahan';

    public $timestamps = false;
}
