<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;

class RiwayatBarangMedis extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'riwayat_barang_medis';

    protected $primaryKey = null;

    protected $keyType = null;

    public $incrementing = false;

    public $timestamps = false;
}
