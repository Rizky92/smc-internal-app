<?php

namespace App\Models\Keuangan\RKAT;

use App\Database\Eloquent\Model;

class PemakaianAnggaranDetail extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'pemakaian_anggaran_detail';

    protected $fillable = [
        'nominal',
        'keterangan',
    ];

    protected $casts = ['nominal' => 'float'];

    protected $searchColumns = ['keterangan'];
}
