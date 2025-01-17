<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PenjualanObatDetail extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'detailjual';

    protected $primaryKey = null;

    protected $keyType = false;

    public $incrementing = false;

    public $timestamps = false;

    public function scopeItemFakturPajak(Builder $query, array $noRawat = []): Builder
    {
        return $query;
    }
}
