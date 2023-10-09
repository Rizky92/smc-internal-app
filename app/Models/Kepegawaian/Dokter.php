<?php

namespace App\Models\Kepegawaian;

use App\Database\Eloquent\Model;

class Dokter extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_dokter';

    protected $keyType = 'string';

    protected $table = 'dokter';

    public $incrementing = false;

    public $timestamps = false;
}
