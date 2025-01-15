<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;

class FakturPajakDitarik extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'faktur_pajak_ditarik';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = false;
}
