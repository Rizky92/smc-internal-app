<?php

namespace App\Models\RekamMedis;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class DemografiPasien extends Model
{
    protected $connection = 'mysql_smc';

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'demografi_pasien';

    public $incrementing = false;

    public $timestamps = false;

    protected $searchColumns = [
        'kecamatan',
        'no_rm',
        'no_rawat',
        'nm_pasien',
        'almt',
        'diagnosa',
        'agama',
        'pendidikan',
        'bahasa',
        'suku',
    ];

    public function scopeLaporanDemografiExcel(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = <<<'SQL'
            kecamatan,
            no_rm,
            no_rawat,
            nm_pasien,
            almt,
            if (sttsumur = 'hr' and umurdaftar < 28, 1, 0) umur_kat_1,
            if ((sttsumur = 'hr' and umurdaftar >= 28) or (sttsumur = 'bl' and umurdaftar < 12), 1, 0) umur_kat_2,
            if (sttsumur = 'th' and umurdaftar between 1 and 4, 1, 0) umur_kat_3,
            if (sttsumur = 'th' and umurdaftar between 5 and 14, 1, 0) umur_kat_4,
            if (sttsumur = 'th' and umurdaftar between 15 and 24, 1, 0) umur_kat_5,
            if (sttsumur = 'th' and umurdaftar between 25 and 44, 1, 0) umur_kat_6,
            if (sttsumur = 'th' and umurdaftar between 45 and 64, 1, 0) umur_kat_7,
            if (sttsumur = 'th' and umurdaftar >= 65, 1, 0) umur_kat_8,
            if (jk = 'P', 1, 0) pr,
            if (jk = 'L', 1, 0) lk,
            diagnosa,
            agama,
            pendidikan,
            bahasa,
            suku
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->whereBetween('tgl_registrasi', [$tglAwal, $tglAkhir]);
    }
}
