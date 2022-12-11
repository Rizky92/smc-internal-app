<?php

namespace App\Models\RekamMedis;

use Illuminate\Database\Eloquent\Model;

class Penjamin extends Model
{
    protected $primaryKey = 'kd_pj';

    protected $keyType = 'string';

    protected $table = 'penjab';

    public $incrementing = false;

    public $timestamps = false;
}
