<?php

namespace App\Models\RekamMedis;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class EpasienUser extends Model
{
    protected $connection = 'mysql_epasien';

    protected $table = 'users';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'email',
        'password',
        'no_rkm_medis',
        'no_ktp',
        'tgl_lahir',
        'no_rkm_medis_verified_by',
    ];

    public function scopeVerifikasiPasien(Builder $query, bool $semuaUser = false ): Builder
    {
        $sqlSelect = <<<'SQL'
            users.id,
            users.name,
            users.email,
            users.no_rkm_medis,
            users.no_ktp,
            users.tgl_lahir,
            users.no_rkm_medis_verified_by
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->when(!$semuaUser, fn(Builder $query): Builder => $query->whereNull('no_rkm_medis'));
    }
}
