<?php

namespace App\Models\Keuangan;

use App\Models\Kepegawaian\Petugas;
use App\Models\RekamMedis\Penjamin;
use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenagihanPiutang extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = null;

    protected $keyType = null;

    protected $table = 'penagihan_piutang';

    public $incrementing = false;

    public $timestamps = false;

    public function penagih(): BelongsTo
    {
        return $this->belongsTo(Petugas::class, 'nip', 'nip');
    }

    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(Petugas::class, 'nip_menyetujui', 'nip');
    }

    public function asuransi(): BelongsTo
    {
        return $this->belongsTo(Penjamin::class, 'kd_pj', 'kd_pj');
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class, 'kd_rek', 'kd_rek');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(PenagihanPiutangDetail::class, 'no_tagihan', 'no_tagihan');
    }

    public function scopeTagihanPiutangAging(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
        SQL;

        return $query;
    }
}
