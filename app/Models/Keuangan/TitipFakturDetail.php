<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TitipFakturDetail extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'detail_titip_faktur';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * @psalm-return BelongsTo<TitipFaktur>
     */
    public function titipFaktur(): BelongsTo
    {
        return $this->belongsTo(TitipFaktur::class, 'no_tagihan', 'no_tagihan');
    }
}
