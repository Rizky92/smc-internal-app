<?php

namespace App\Models\Aplikasi;

use App\Database\Eloquent\Concerns\Searchable;
use Spatie\Permission\Models\Role as BaseModel;

class Role extends BaseModel
{
    use Searchable;

    protected $searchColumns = [
        'name',
    ];

    protected $connection = 'mysql_smc';
}
