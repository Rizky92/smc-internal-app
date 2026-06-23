<?php

namespace App\Models\Kepegawaian;

use App\Database\Eloquent\Model;

class Departemen extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'dep_id';

    protected $keyType = 'string';

    protected $table = 'departemen';

    public $incrementing = false;

    public $timestamps = false;
}
