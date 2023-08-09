<?php

namespace App\Models\Keuangan;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Model;

class BayarPiutang extends Model
{
    use Searchable, Sortable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'bayar_piutang';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tgl_bayar',
        'no_rkm_medis',
        'catatan',
        'no_rawat',
        'kd_rek',
        'kd_rek_kontra',
        'besar_cicilan',
        'diskon_piutang',
        'kd_rek_diskon_piutang',
        'tidak_terbayar',
        'kd_rek_tidak_terbayar',
    ];
}
