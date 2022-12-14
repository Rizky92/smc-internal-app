<?php

namespace App\Models\Aplikasi;

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

    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope(function (Builder $query) {
            return $query->selectRaw('AES_DECRYPT(id_user, "nur") user_id, petugas.nama, jabatan.nm_jbtn, user.*')
                ->join('petugas', DB::raw('AES_DECRYPT(id_user, "nur")'), '=', 'petugas.nip')
                ->join('jabatan', 'petugas.kd_jbtn', '=', 'jabatan.kd_jbtn')
                ->orderBy(DB::raw('AES_DECRYPT(id_user, "nur")'));
        });
    }
    
    /**
     * @return static
     */
    public static function findByNRP(string $nrp)
    {
        if (empty($nrp)) return new static;

        return (new static)
            ->where(DB::raw('AES_DECRYPT(id_user, "nur")'), $nrp)
            ->first();
    }

    public function scopeDenganPencarian(Builder $query, string $cari): Builder
    {
        return $query->where(DB::raw('AES_DECRYPT(id_user, "nur")'), 'LIKE', "%{$cari}%")
            ->orWhere('petugas.nama', 'LIKE', "%{$cari}%")
            ->orWhere('jabatan.nm_jbtn', 'LIKE', "%{$cari}%");
    }
}
