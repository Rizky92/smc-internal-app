<?php

namespace App\Models\Aplikasi;

use App\Support\Traits\Eloquent\Searchable;
use Spatie\Permission\Models\Permission as BaseModel;

class Permission extends BaseModel
{
    use Searchable;
    
    protected $connection = 'mysql_smc';

    protected $searchColumns = ['name'];
}
