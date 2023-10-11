<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;

class TindakanRalanPerawat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'rawat_jl_pr';

    public $incrementing = false;

    public $timestamps = false;
}
