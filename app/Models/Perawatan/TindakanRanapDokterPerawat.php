<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;

class TindakanRanapDokterPerawat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'rawat_inap_drpr';

    public $incrementing = false;

    public $timestamps = false;
}
