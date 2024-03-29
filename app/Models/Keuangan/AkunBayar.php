<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;

class AkunBayar extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'akun_bayar';

    protected $primaryKey = null;

    protected $keyType = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'ppn' => 'float',
    ];
}
