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

    protected $table = 'laporan_statistik';

    public $incrementing = false;

    public $timestamps = false;

    /** @var array */
    protected $searchColumns = [
        'no_rawat',
        'no_rm',
        'nm_pasien',
        'no_ktp',
        'jk',
        'umur',
        'agama',
        'suku',
        'status_lanjut',
        'status_poli',
        'nm_poli',
        'nm_dokter',
        'status',
        'diagnosa_awal',
        'kd_diagnosa',
        'nm_diagnosa',
        'kd_tindakan_ralan',
        'nm_tindakan_ralan',
        'kd_tindakan_ranap',
        'nm_tindakan_ranap',
        'lama_operasi',
        'rujukan_masuk',
        'dokter_pj',
        'kelas',
        'penjamin',
        'status_bayar',
        'status_pulang_ranap',
        'rujuk_keluar_rs',
        'alamat',
        'no_hp',
        'kunjungan_ke',
    ];
}
