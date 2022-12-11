<?php

namespace App\Models\Aplikasi;

use Spatie\Permission\Models\Permission as BaseModel;

class Permission extends BaseModel
{
    protected $connection = 'mysql_smc';
}
