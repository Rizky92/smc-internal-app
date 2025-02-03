<?php

namespace App\Models\Aplikasi;

use App\Casts\BooleanCast;
use App\Database\Eloquent\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lab404\Impersonate\Models\Impersonate;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    use Impersonate;
    use Notifiable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = 'id_user';

    protected $keyType = 'string';

    protected $table = 'user';

    public $incrementing = false;

    public $timestamps = false;

    protected $hidden = ['password'];

    protected $guarded = ['id_user', 'password'];

    protected function searchColumns(): array
    {
        return [
            'pegawai.nik',
            'pegawai.nama',
            DB::raw('coalesce(jabatan.nm_jbtn, spesialis.nm_sps, pegawai.jbtn, "")'),
            DB::raw('(case when petugas.nip is not null then "Petugas" when dokter.kd_dokter is not null then "Dokter" else "-" end)'),
        ];
    }

    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope(function (Builder $query) {
            $sqlSelect = <<<'SQL'
                trim(pegawai.nik) nik, pegawai.nama nama, coalesce(jabatan.nm_jbtn, spesialis.nm_sps, pegawai.jbtn) jbtn,
                (case when petugas.nip is not null then 'Petugas' when dokter.kd_dokter is not null then 'Dokter' else '-' end) jenis,
                user.id_user id_user, user.password `password`
                SQL;

            return $query
                ->selectRaw($sqlSelect)
                ->join('pegawai', DB::raw('trim(AES_DECRYPT(id_user, "nur"))'), '=', DB::raw('trim(pegawai.nik)'))
                ->leftJoin('petugas', DB::raw('trim(pegawai.nik)'), '=', DB::raw('trim(petugas.nip)'))
                ->leftJoin('jabatan', 'petugas.kd_jbtn', '=', 'jabatan.kd_jbtn')
                ->leftJoin('dokter', DB::raw('trim(pegawai.nik)'), '=', DB::raw('trim(dokter.kd_dokter)'))
                ->leftJoin('spesialis', 'dokter.kd_sps', '=', 'spesialis.kd_sps')
                ->orderBy(DB::raw('trim(pegawai.nik)'));
        });
    }

    public function scopeWithHakAkses(Builder $query): Builder
    {
        $casts = collect(Schema::connection('mysql_sik')
            ->getColumnListing('user'))
            ->skip(2)
            ->flip()
            ->map(fn ($_, string $f): string => BooleanCast::class)
            ->all();

        return $query->selectRaw('user.*')->withCasts($casts);
    }

    public function scopeTampilkanYangMemilikiHakAkses(Builder $query, bool $hakAkses = false): Builder
    {
        $db = DB::connection('mysql_smc')->getDatabaseName();

        // Laravel tidak bisa melakukan query `whereHas` apabila berbeda koneksi database
        // sehingga perlu melakukan pengecekan secara manual dengan mengkonversi query
        // builder menjadi bentuk raw SQL query sebagai workaround masalah tersebut.
        $sqlHasRoles = DB::table("{$db}.model_has_roles")
            ->whereRaw("model_type = 'User'")
            ->whereRaw('model_has_roles.model_id = user.id_user')
            ->toSql();

        $sqlHasPermissions = DB::table("{$db}.model_has_permissions")
            ->whereRaw("model_type = 'User'")
            ->whereRaw('model_has_permissions.model_id = user.id_user')
            ->toSql();

        return $query->when($hakAkses, fn (Builder $q): Builder => $q
            ->whereRaw("exists ({$sqlHasRoles})")
            ->orWhereRaw("exists ({$sqlHasPermissions})"));
    }

    /**
     * @return Model|static|null
     */
    public static function findByNRP(string $nrp, array $columns = ['*'])
    {
        if (empty($nrp)) {
            return new static;
        }

        return static::query()
            ->where(DB::raw('trim(pegawai.nik)'), $nrp)
            ->first($columns);
    }

    /**
     * @return static
     */
    public static function rawFindByNRP(string $nrp, array $columns = ['*']): self
    {
        if (empty($nrp)) {
            return new static;
        }

        return static::query()
            ->withoutGlobalScopes()
            ->withHakAkses()
            ->whereRaw('AES_DECRYPT(user.id_user, ?) = ?', [config('khanza.app.userkey'), $nrp])
            ->first($columns);
    }
}
