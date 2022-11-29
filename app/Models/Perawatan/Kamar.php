<?php

namespace App\Models\Perawatan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kamar extends Model
{
    protected $primaryKey = 'kd_kamar';

    protected $keyType = 'string';

    protected $table = 'kamar';

    public $incrementing = false;

    public $timestamps = false;

    public function bangsal(): BelongsTo
    {
        return $this->belongsTo(Bangsal::class, 'kd_bangsal', 'kd_bangsal');
    }

    public function rawatInap(): BelongsToMany
    {
        return $this->belongsToMany(Registrasi::class, 'kamar_inap', 'kd_kamar', 'no_rawat')
            ->withPivot(RawatInap::$pivotColumns)
            ->using(RawatInap::class);
    }
}
