<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;

class KategoriPerawatan extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'kategori_perawatan';

    protected $primaryKey = 'kd_kategori';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;
}
