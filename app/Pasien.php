<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pasien extends Model
{
    protected $primaryKey = 'no_rkm_medis';

    protected $keyType = 'string';

    protected $table = 'pasien';

    public $incrementing = false;

    public $timestamps = false;

    public function suku()
    {
        return $this->belongsTo('App\Suku', 'suku_bangsa', 'id');
    }

    public function kelurahan()
    {
        return $this->belongsTo('App\Kelurahan', 'kd_kel', 'kd_kel');
    }

    public function kecamatan()
    {
        return $this->belongsTo('App\Kecamatan', 'kd_kec', 'kd_kec');
    }

    public function kabupaten()
    {
        return $this->belongsTo('App\Kabupaten', 'kd_kab', 'kd_kab');
    }

    public function provinsi()
    {
        return $this->belongsTo('App\Provinsi', 'kd_prop', 'kd_prop');
    }
}
