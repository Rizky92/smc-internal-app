<?php

namespace App;

use Spatie\Permission\Models\Permission as BaseModel;

class Permission extends BaseModel
{
    protected $connection = 'mysql_smc';
}
