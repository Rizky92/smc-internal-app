<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FakturPajakDitarikDetail extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'faktur_pajak_ditarik_detail';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = false;

    public function fakturPajak(): BelongsTo
    {
        return $this->belongsTo(FakturPajakDitarik::class, 'faktur_pajak_ditarik_id', 'id');
    }
}
