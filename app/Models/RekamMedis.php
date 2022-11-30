<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekamMedis extends Model
{
    protected $connection = 'mysql_smc';

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'rekam_medis';

    public $incrementing = false;

    public $timestamps = false;
}
