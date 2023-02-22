<?php

namespace App\Models\Aplikasi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TrackerMenu extends Model
{
    protected $connection = 'mysql_smc';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'trackermenu';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeLihatAktivitasUser(Builder $query, string $userId): Builder
    {
        $db = DB::connection('mysql_sik')->getDatabaseName();
        
        $sqlSelect = "
            trackermenu.waktu,
            trackermenu.breadcrumbs,
            trackermenu.route_name,
            trackermenu.ip_address,
            pegawai.nama,
            coalesce(jabatan.nm_jabatan, pegawai.jbtn) jabatan
        ";

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin(DB::raw("{$db}.pegawai pegawai"), 'trackermenu.user_id', '=', 'pegawai.nik')
            ->leftJoin(DB::raw("{$db}.petugas petugas"), 'pegawai.nik', '=', 'petugas.nip')
            ->leftJoin(DB::raw("{$db}.jabatan jabatan"), 'petugas.kd_jbtn', '=', 'jabatan.kd_jbtn')
            ->where('user_id', $userId);
    }
}
