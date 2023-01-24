<?php

namespace App\Models\RekamMedis;

use App\Support\Traits\Eloquent\Searchable;
use Illuminate\Database\Eloquent\Model;

class StatistikRekamMedis extends Model
{
    use Searchable;

    protected $connection = 'mysql_smc';

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'rekam_medis';

    public $incrementing = false;

    public $timestamps = false;

    protected $searchColumns = [
        'no_rawat',
        'no_rm',
        'pasien',
        'nik',
        'agama',
        'suku',
        'status_rawat',
        'status_poli',
        'asal_poli',
        'dokter_poli',
        'status_ralan',
        'diagnosa_awal',
        'kd_diagnosa',
        'nm_diagnosa',
        'kd_tindakan_ralan',
        'nm_tindakan_ralan',
        'kd_tindakan_ranap',
        'nm_tindakan_ranap',
        'dokter_pj',
        'kelas',
        'jenis_bayar',
        'status_bayar',
        'status_pulang_ranap',
        'rujuk_keluar_rs',
        'alamat',
        'no_hp',
    ];
}
