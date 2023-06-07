<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kode_sat';

    protected $keyType = 'string';

    protected $table = 'kodesatuan';

    public $incrementing = false;

    public $timestamps = false;
}
