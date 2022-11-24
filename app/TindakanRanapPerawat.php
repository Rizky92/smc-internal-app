<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TindakanRanapPerawat extends Pivot
{
    protected $table = 'rawat_inap_pr';

    public $incrementing = false;

    public $timestamps = false;

    public static $pivotColumns = [
        'nip',
        'tgl_perawatan',
        'jam_rawat',
        'material',
        'bhp',
        'tarif_tindakanpr',
        'kso',
        'menejemen',
        'biaya_rawat',
        'stts_bayar',
    ];

    public function perawat()
    {
        return $this->belongsTo('App\Petugas', 'nip', 'nip');
    }
}
