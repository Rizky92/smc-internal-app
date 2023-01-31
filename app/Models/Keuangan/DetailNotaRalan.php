<?php

namespace App\Models\Keuangan;

use Illuminate\Database\Eloquent\Model;

class DetailNotaRalan extends Model
{
    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'detail_nota_jalan';

    public $incrementing = false;

    public $timestamps = false;
}
