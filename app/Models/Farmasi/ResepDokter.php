<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;

class ResepDokter extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_resep';

    protected $keyType = 'string';

    protected $table = 'resep_dokter';

    public $incrementing = false;

    public $timestamps = false;

    protected $searchColumns = [
        'no_resep',
        'kode_brng',
        'aturan_pakai',
    ];
}
