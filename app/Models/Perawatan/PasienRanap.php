<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;

class PasienRanap extends Model
{
    protected $connection = 'mysql_smc';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'laporan_pasien_ranap';

    public $incrementing = false;

    public $timestamps = false;

    protected $searchColumns = [
        'no_rawat',
        'ruangan',
        'kelas',
        'no_rkm_medis',
        'data_pasien',
        'png_jawab',
        'nm_poli',
        'nm_dokter',
        'stts_pulang',
        'dpjp',
    ];
}
