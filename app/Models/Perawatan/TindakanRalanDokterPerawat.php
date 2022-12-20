<?php

namespace App\Models\Perawatan;

use App\Models\Dokter;
use App\Models\Petugas;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TindakanRalanDokterPerawat extends Pivot
{
    protected $table = 'rawat_jl_drpr';

    public $incrementing = false;

    public $timestamps = false;

    public static $pivotColumns = [
        'kd_dokter',
        'nip',
        'tgl_perawatan',
        'jam_rawat',
        'material',
        'bhp',
        'tarif_tindakandr',
        'tarif_tindakanpr',
        'kso',
        'menejemen',
        'biaya_rawat',
        'stts_bayar',
    ];

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

    public function perawat(): BelongsTo
    {
        return $this->belongsTo(Petugas::class, 'nip', 'nip');
    }
}
