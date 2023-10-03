<?php

namespace App\Models\Keuangan;

use App\Models\Perawatan\RegistrasiPasien;
use App\Database\Eloquent\Concerns\Searchable;
use App\Database\Eloquent\Concerns\Sortable;
use Illuminate\Database\Eloquent\Builder;
use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class PenagihanPiutangDetail extends Model
{
    use Sortable, Searchable;

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
