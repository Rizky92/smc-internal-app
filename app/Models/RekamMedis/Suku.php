<?php

namespace App\Models\RekamMedis;

use Illuminate\Database\Eloquent\Model;

class Suku extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'id';

    protected $table = 'suku_bangsa';

    public $timestamps = false;
}
