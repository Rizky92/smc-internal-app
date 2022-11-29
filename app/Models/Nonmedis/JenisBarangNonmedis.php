<?php

namespace App\Models\Nonmedis;

use Illuminate\Database\Eloquent\Model;

class JenisBarangNonmedis extends Model
{
    protected $primaryKey = 'kd_jenis';

    protected $keyType = 'string';

    protected $table = 'ipsrsjenisbarang';

    public $incrementing = false;

    public $timestamps = false;
}
