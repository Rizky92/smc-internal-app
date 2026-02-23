<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;

class PiutangObatDetail extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'PiutangObatDetail';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = false;

    public $timestamps = false;
}
