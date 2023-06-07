<?php

namespace App\Models\RekamMedis;

use Illuminate\Database\Eloquent\Model;

class Kelurahan extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_kel';

    protected $keyType = 'int';

    protected $table = 'kelurahan';

    public $incrementing = false;

    public $timestamps = false;
}
