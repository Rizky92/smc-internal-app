<?php

namespace App\Models;

use App\Database\Eloquent\Model;

class TemporaryExport extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'temporaries';

    protected $primaryKey = 'id';

    protected $keyType = 'int';
}
