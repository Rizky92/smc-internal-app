<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;

class RekeningTahun extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'rekeningtahun';

    public $incrementing = false;

    public $timestamps = false;
}
