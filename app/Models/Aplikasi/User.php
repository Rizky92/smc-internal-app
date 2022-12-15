<?php

namespace App\Models\Aplikasi;

use App\Support\Searchable\Searchable;
use App\Support\Searchable\SearchContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles, Searchable;

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

    protected function searchColumns(): array
    {
        return [
            'petugas.nip',
            'petugas.nama',
            'jabatan.nm_jbtn',
        ];
    }

    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope(function (Builder $query) {
            return $query->selectRaw('petugas.nip, petugas.nama, jabatan.nm_jbtn, user.id_user, user.password')
                ->join('petugas', DB::raw('AES_DECRYPT(id_user, "nur")'), '=', 'petugas.nip')
                ->join('jabatan', 'petugas.kd_jbtn', '=', 'jabatan.kd_jbtn')
                ->orderBy('petugas.nip');
        });
    }
    
    /**
     * @return static
     */
    public static function findByNRP(string $nrp, array $columns = ['*'])
    {
        if (empty($nrp)) return new static;

        return (new static)
            ->where(DB::raw('AES_DECRYPT(id_user, "nur")'), $nrp)
            ->first($columns);
    }
}
