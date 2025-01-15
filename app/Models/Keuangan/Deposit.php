<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'deposit';

    protected $primaryKey = 'no_deposit';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;
}
