<?php

namespace App\Models\RekamMedis;

use App\Database\Eloquent\Model;

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
        'tgl_lahir'
    ];
}
