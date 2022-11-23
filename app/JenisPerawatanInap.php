<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JenisPerawatanInap extends Model
{
    protected $primaryKey = 'kd_jenis_prw';

    protected $keyType = 'string';

    protected $table = 'jns_perawatan_inap';

    public $incrementing = false;

    public $timestamps = false;
}
