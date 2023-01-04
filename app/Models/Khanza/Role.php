<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $connection = 'mysql_smc';

    protected $casts = [
        'perizinan' => 'json'
    ];
}
