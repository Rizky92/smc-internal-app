<?php

namespace App\Models\Perawatan;

use App\Models\Dokter;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TindakanRalanDokter extends Pivot
{
    protected $table = 'rawat_jl_dr';

    public $incrementing = false;

    public $timestamps = false;

    public static $pivotColumns = [
        'kd_dokter',
        'tgl_perawatan',
        'jam_rawat',
        'material',
        'bhp',
        'tarif_tindakandr',
        'kso',
        'menejemen',
        'biaya_rawat',
        'stts_bayar',
    ];

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }
}
