<?php

namespace App\Models\Kepegawaian;

use App\Database\Eloquent\Model;
use App\Models\Antrian\Jadwal;
use App\Models\Perawatan\RegistrasiPasien;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dokter extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_dokter';

    protected $keyType = 'string';

    protected $table = 'dokter';

    public $incrementing = false;

    public $timestamps = false;

    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'kd_dokter', 'kd_dokter');
    }

    public function registrasi(): HasMany
{
    return $this->hasMany(RegistrasiPasien::class, 'kd_dokter', 'kd_dokter');
}
}
