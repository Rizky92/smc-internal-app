<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;

class TindakanRalanDokter extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'rawat_jl_dr';

    public $incrementing = false;

    public $timestamps = false;
}
