<?php

namespace App\Models\RekamMedis;

use App\Database\Eloquent\Model;

class Suku extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $table = 'suku_bangsa';

    public $incrementing = true;

    public $timestamps = false;
}
