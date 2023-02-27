<?php

namespace App\Models\Keuangan;

use Illuminate\Database\Eloquent\Model;

class NotaRanap extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'nota_inap';

    public $incrementing = false;

    public $timestamps = false;
}
