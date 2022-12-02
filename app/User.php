<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $primaryKey = 'id_user';

    protected $keyType = 'string';

    protected $table = 'user';

    public $incrementing = false;

    public $timestamps = false;

    protected $hidden = [
        'password',
    ];

    protected $with = [
        'roles',
    ];

    protected $appends = [
        'user_id',
        'nama',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(function (Builder $query) {
            $query->selectRaw('AES_DECRYPT(id_user, "nur") user_id, petugas.nama, jabatan.nm_jbtn, user.*')
                ->join('petugas', DB::raw('AES_DECRYPT(id_user, "nur")'), '=', 'petugas.nip')
                ->join('jabatan', 'petugas.kd_jbtn', '=', 'jabatan.kd_jbtn');
        });
    }
}
