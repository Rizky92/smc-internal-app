<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;

class TitipFakturDetail extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'detail_titip_faktur';

    public $incrementing = false;

    public $timestamps = false;

    public function titipFaktur()
    {
        return $this->belongsTo(TitipFaktur::class, 'no_tagihan', 'no_tagihan');
    }
}
