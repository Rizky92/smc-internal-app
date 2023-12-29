<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;
use App\Models\Antrian\Jadwal;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'kd_poli', 'kd_poli');
    }
}
