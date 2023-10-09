<?php

namespace App\Models\Kepegawaian;

use App\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $table = 'pegawai';

    public $incrementing = true;

    public $timestamps = false;
}
