<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;
use App\Models\Perawatan\RegistrasiPasien;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenagihanPiutangDetail extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_tagihan';

    protected $keyType = 'string';

    protected $table = 'detail_penagihan_piutang';

    public $incrementing = false;

    public $timestamps = false;

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(PenagihanPiutang::class, 'no_tagihan', 'no_tagihan');
    }

    public function registrasi(): BelongsTo
    {
        return $this->belongsTo(RegistrasiPasien::class, 'no_rawat', 'no_rawat');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(BayarPiutang::class, 'no_rawat', 'no_rawat');
    }
}
