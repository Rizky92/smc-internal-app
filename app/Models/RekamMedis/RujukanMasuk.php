<?php

namespace App\Models\RekamMedis;

use App\Database\Eloquent\Model;

class RujukanMasuk extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'rujuk_masuk';

    public $incrementing = false;

    public $timestamps = false;
}
