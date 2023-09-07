<?php

namespace App\Models\Perawatan;

use App\Support\Eloquent\Model;

class Penyakit extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_penyakit';

    protected $keyType = 'string';

    protected $table = 'penyakit';

    public $incrementing = false;

    public $timestamps = false;
}
