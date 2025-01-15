<?php

namespace App\Models\Keuangan\Master;

use App\Database\Eloquent\Model;

class SatuanUkuranPajak extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'satuan_ukuran_pajak';

    protected $primaryKey = 'kode_satuan_pajak';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;
}
