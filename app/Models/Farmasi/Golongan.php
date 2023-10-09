<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;

class Golongan extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'golongan_barang';

    protected $primaryKey = 'kode';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $searchColumns = ['kode', 'nama'];
}
