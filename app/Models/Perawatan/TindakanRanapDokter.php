<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;

class TindakanRanapDokter extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'rawat_inap_dr';

    public $incrementing = false;

    public $timestamps = false;
}
