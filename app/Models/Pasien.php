<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pasien extends Model
{
    protected $primaryKey = 'no_rkm_medis';

    protected $keyType = 'string';

    protected $table = 'pasien';

    public $incrementing = false;

    public $timestamps = false;

    public function suku(): BelongsTo
    {
        return $this->belongsTo(Suku::class, 'suku_bangsa', 'id');
    }

    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class, 'kd_kel', 'kd_kel');
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kd_kec', 'kd_kec');
    }

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Kabupaten::class, 'kd_kab', 'kd_kab');
    }

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class, 'kd_prop', 'kd_prop');
    }
}
