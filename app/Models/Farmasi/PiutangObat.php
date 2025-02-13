<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;

class PiutangObat extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'PiutangObat';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = false;

    public $timestamps = false;
}
