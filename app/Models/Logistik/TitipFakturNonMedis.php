<?php

namespace App\Models\Logistik;

use App\Database\Eloquent\Model;

class TitipFakturNonMedis extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'ipsrs_titip_faktur';

    public $incrementing = false;

    public $timestamps = false;
}
