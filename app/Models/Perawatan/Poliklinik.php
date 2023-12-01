<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poliklinik extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_poli';

    protected $keyType = 'string';

    protected $table = 'poliklinik';

    public $incrementing = false;

    public $timestamps = false;

    public function registrasi(): HasMany
    {
        return $this->hasMany(RegistrasiPasien::class, 'kd_poli', 'kd_poli');
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'kd_poli', 'kd_poli');
    }
}
