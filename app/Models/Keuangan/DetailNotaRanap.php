<?php

namespace App\Models\Keuangan;

use Illuminate\Database\Eloquent\Model;

class DetailNotaRanap extends Model
{
    protected $connection = 'mysql_sik';
    
    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'detail_nota_inap';

    public $incrementing = false;

    public $timestamps = false;
}
