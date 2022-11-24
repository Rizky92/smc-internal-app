<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TindakanRanapDokterPerawat extends Pivot
{
    protected $table = 'rawat_inap_drpr';

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
    ];

    public function dokter()
    {
        return $this->belongsTo('App\Dokter', 'kd_dokter', 'kd_dokter');
    }

    public function perawat()
    {
        return $this->belongsTo('App\Petugas', 'nip', 'nip');
    }
}
