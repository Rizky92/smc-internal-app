<?php

namespace App;

use Spatie\Permission\Models\Role as BaseModel;

class Role extends BaseModel
{
    protected $connection = 'mysql_smc';

    
}
