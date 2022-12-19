<?php

namespace App\Models\Perawatan;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RawatInap extends Pivot
{
    protected $table = 'kamar_inap';

    public $timestamps = false;

    public static $pivotColumns = [
        'trf_kamar',
        'diagnosa_awal',
        'diagnosa_akhir',
        'tgl_masuk',
        'jam_masuk',
        'tgl_keluar',
        'jam_keluar',
        'lama',
        'ttl_biaya',
        'stts_pulang',
    ];
}
