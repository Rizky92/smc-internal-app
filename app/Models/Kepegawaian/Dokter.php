<?php

namespace App\Models\Kepegawaian;

use App\Database\Eloquent\Model;

class Dokter extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_dokter';

    protected $keyType = 'string';

    protected $table = 'dokter';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * @psalm-return \Illuminate\Database\Eloquent\Relations\HasMany<Jadwal>
     */
    public function jadwal(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Jadwal::class, 'kd_dokter', 'kd_dokter');
    }

    /**
     * @psalm-return \Illuminate\Database\Eloquent\Relations\HasMany<RegistrasiPasien>
     */
    public function registrasi(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(RegistrasiPasien::class, 'kd_dokter', 'kd_dokter');
}
}


