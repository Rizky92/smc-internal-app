<?php

namespace App\Models\Aplikasi;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Lab404\Impersonate\Models\Impersonate;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles, Searchable, Sortable, Impersonate;

    protected $primaryKey = 'id_user';

    protected $keyType = 'string';

    protected $table = 'user';

    public $incrementing = false;

    public $timestamps = false;

    protected $hidden = ['password'];

    protected $guarded = ['id_user', 'password'];

    protected $with = [
        'roles.permissions',
        'permissions',
    ];

    protected $searchColumns = [
        'pegawai.nik',
        'pegawai.nama',
        'jabatan.nm_jbtn',
    ];

    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope(function (Builder $query) {
            $sqlSelect = "
                trim(pegawai.nik) nik,
                pegawai.nama nama,
                coalesce(jabatan.nm_jbtn, spesialis.nm_sps, pegawai.jbtn) jbtn,
                (case when petugas.nip is not null then 'Petugas' when dokter.kd_dokter is not null then 'Dokter' else '-' end) jenis,
                user.id_user id_user,
                user.password password
            ";

            return $query->selectRaw($sqlSelect)
                ->join('pegawai', DB::raw('AES_DECRYPT(id_user, "nur")'), '=', DB::raw('trim(pegawai.nik)'))
                ->leftJoin('petugas', DB::raw('trim(pegawai.nik)'), '=', DB::raw('trim(petugas.nip)'))
                ->leftJoin('jabatan', 'petugas.kd_jbtn', '=', 'jabatan.kd_jbtn')
                ->leftJoin('dokter', DB::raw('trim(pegawai.nik)'), '=', DB::raw('trim(dokter.kd_dokter)'))
                ->leftJoin('spesialis', 'dokter.kd_sps', '=', 'spesialis.kd_sps')
                ->orderBy(DB::raw('trim(pegawai.nik)'));
        });
    }

    public function scopeWithHakAkses(Builder $query)
    {
        return $query->selectRaw('user.*');
    }

    public static function findByNRP(string $nrp, array $columns = ['*'])
    {
        if (empty($nrp)) return new static;

        return static::query()
            ->where('pegawai.nik', $nrp)
            ->first($columns);
    }

    public static function rawFindByNRP(string $nrp, array $columns = [''])
    {
        if (empty($nrp)) return new static;

        return static::query()
            ->withoutGlobalScopes()
            ->withHakAkses()
            ->whereRaw("AES_DECRYPT(user.id_user, 'nur') = ?", $nrp)
            ->first($columns);
    }
}
