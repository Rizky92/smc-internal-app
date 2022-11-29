<?php

namespace App\Models\Perawatan;

use Illuminate\Database\Eloquent\Model;

class JenisPerawatanRalan extends Model
{
    protected $primaryKey = 'kd_jenis_prw';

    protected $keyType = 'string';

    protected $table = 'jns_perawatan';

    public $incrementing = false;

    public $timestamps = false;
}
