<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;

class PengeluaranHarian extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'pengeluaran_harian';

    protected $primaryKey = 'no_keluar';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;
}
