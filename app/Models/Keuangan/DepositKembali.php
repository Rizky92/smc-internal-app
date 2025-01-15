<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;

class DepositKembali extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'DepositKembali';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = false;

    public $timestamps = false;
}
