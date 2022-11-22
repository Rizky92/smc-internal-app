<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Poliklinik extends Model
{
    protected $primaryKey = 'kd_poli';

    protected $keyType = 'string';

    protected $table = 'poliklinik';

    public $incrementing = false;

    public $timestamps = false;

    public function registrasi()
    {
        return $this->hasMany(Registrasi::class, 'kd_poli', 'kd_poli');
    }
}
