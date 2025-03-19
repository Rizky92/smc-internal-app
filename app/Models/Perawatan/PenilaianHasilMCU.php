<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;

class PenilaianHasilMCU extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'penilaian_mcu';

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;
}
