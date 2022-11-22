<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Asuransi extends Model
{
    protected $primaryKey = 'kd_pj';

    protected $keyType = 'string';

    protected $table = 'penjab';

    public $incrementing = false;

    public $timestamps = false;

    public function pasien()
    {
        return $this->hasMany(Pasien::class, 'kd_pj', 'kd_pj');
    }

    public function registrasi()
    {
        return $this->hasMany(Registrasi::class, 'kd_pj', 'kd_pj');
    }
}
