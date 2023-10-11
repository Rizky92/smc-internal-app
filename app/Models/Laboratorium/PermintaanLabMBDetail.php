<?php

namespace App\Models\Laboratorium;

use App\Database\Eloquent\Model;

class PermintaanLabMBDetail extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'permintaan_detail_permintaan_labmb';

    protected $primaryKey = 'noorder';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;
}
