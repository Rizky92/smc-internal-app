<?php

namespace App\Models\Keuangan;

use Illuminate\Database\Eloquent\Model;

class NotaRalan extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'nota_jalan';

    public $incrementing = false;

    public $timestamps = false;
}
