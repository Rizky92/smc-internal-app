<?php

namespace App\Models;

use App\Database\Eloquent\Model;

class Export extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'exports';

    protected $primaryKey = 'id';
}
