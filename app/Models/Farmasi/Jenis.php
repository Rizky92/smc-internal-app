<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;

class Jenis extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'jenis';

    protected $primaryKey = 'kdjns';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $searchColumns = ['kdjns', 'nama', 'keterangan'];
}
