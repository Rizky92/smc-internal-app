<?php

namespace App\Models\RekamMedis;

use Illuminate\Database\Eloquent\Model;

class Penjamin extends Model
{
    protected $connection = 'mysql_sik';
    
    protected $primaryKey = 'kd_pj';

    protected $keyType = 'string';

    protected $table = 'penjab';

    public $incrementing = false;

    public $timestamps = false;
}
