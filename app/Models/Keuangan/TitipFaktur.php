<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;

class TitipFaktur extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'titip_faktur';

    protected $primaryKey = 'no_tagihan';

    public $incrementing = false;

    public $timestamps = false;
}
