<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'kategori_barang';

    protected $primaryKey = 'kode';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $searchColumns = ['kode', 'nama'];
}
