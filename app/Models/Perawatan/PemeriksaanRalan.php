<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;

class PemeriksaanRalan extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'pemeriksaan_ralan';

    protected $primaryKey = false;

    protected $keyType = false;

    public $incrementing = false;

    public $timestamps = false;
}
