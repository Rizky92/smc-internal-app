<?php

namespace App\Models\Perawatan;

use Illuminate\Database\Eloquent\Model;

class Penyakit extends Model
{
    protected $primaryKey = 'kd_penyakit';

    protected $keyType = 'string';

    protected $table = 'penyakit';

    public $incrementing = false;

    public $timestamps = false;
}
