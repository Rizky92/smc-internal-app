<?php

namespace App\Models\Kepegawaian;

use App\Support\Eloquent\Model;

class Petugas extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'nip';

    protected $keyType = 'string';

    protected $table = 'petugas';

    public $incrementing = false;

    public $timestamps = false;
}
