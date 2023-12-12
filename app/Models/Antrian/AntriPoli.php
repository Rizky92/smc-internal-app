<?php

namespace App\Models\Antrian;

use App\Database\Eloquent\Model;

class AntriPoli extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'antripoli';

    protected $primaryKey = null;

    public $timestamps = false;

    protected $fillable = [
        'status',
    ];
}
