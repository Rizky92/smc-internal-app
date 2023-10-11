<?php

namespace App\Models;

use App\Database\Eloquent\Model;

class Perusahaan extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'perusahaan_pasien';

    protected $primaryKey = 'kode_perusahaan';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;
}
