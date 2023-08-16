<?php

namespace App\Models\Keuangan;

use App\Models\Kepegawaian\Petugas;
use App\Models\RekamMedis\Penjamin;
use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenagihanPiutang extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_tagihan';

    protected $keyType = 'string';

    protected $table = 'penagihan_piutang';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'status',
    ];

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
}
