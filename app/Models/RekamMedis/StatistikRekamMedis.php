<?php

namespace App\Models\RekamMedis;

use Illuminate\Database\Eloquent\Model;

class StatistikRekamMedis extends Model
{
    protected $connection = 'mysql_smc';

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'rekam_medis';

    public $incrementing = false;

    public $timestamps = false;
}
