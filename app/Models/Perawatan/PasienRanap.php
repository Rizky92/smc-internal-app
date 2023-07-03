<?php

namespace App\Models\Perawatan;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Model;

class PasienRanap extends Model
{
    use Searchable, Sortable;

    protected $connection = 'mysql_smc';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'laporan_pasien_ranap';

    public $incrementing = false;

    public $timestamps = false;

    /** 
     * @var string[]
     */
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
