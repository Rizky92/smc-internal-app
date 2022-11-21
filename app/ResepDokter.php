<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ResepDokter extends Pivot
{
    protected $table = 'resep_dokter';

    public static $pivotColumns = [
        'jml',
        'aturan_pakai',
    ];
}
