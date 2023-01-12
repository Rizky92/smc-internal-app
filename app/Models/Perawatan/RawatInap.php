<?php

namespace App\Models\Perawatan;

use Illuminate\Database\Eloquent\Model;

class RawatInap extends Model
{
    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'kamar_inap';

    public $incrementing = false;

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
