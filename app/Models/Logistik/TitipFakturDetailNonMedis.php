<?php

namespace App\Models\Logistik;

use App\Database\Eloquent\Model;

class TitipFakturDetailNonMedis extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'ipsrs_detail_titip_faktur';

    public $incrementing = false;

    public $timestamps = false;
}
