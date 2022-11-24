<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RawatInap extends Pivot
{
    protected $table = 'kamar_inap';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'tgl_masuk' => 'date',
        'jam_masuk' => 'datetime',
        'tgl_keluar' => 'date',
        'jam_keluar' => 'datetime',
    ];

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
