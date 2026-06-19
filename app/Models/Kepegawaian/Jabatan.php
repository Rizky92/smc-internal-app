<?php

namespace App\Models\Kepegawaian;

use App\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_jbtn';

    protected $keyType = 'string';

    protected $table = 'jabatan';

    public $incrementing = false;

    public $timestamps = false;
}
