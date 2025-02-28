<?php

namespace App\Models\Keuangan\RKAT;

use App\Casts\Year;
use App\Database\Eloquent\Model;
use App\Models\Bidang;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class AnggaranBidang extends Model
{
    use HasRelationships;

    protected $connection = 'mysql_smc';

    protected $table = 'anggaran_bidang';

    protected $fillable = [
        'anggaran_id',
        'bidang_id',
        'tahun',
        'nominal_anggaran',
    ];

    protected $casts = [
        'tahun'            => Year::class,
        'nominal_anggaran' => 'float',
    ];

    public function scopeAnggaranPerBidangUnit(Builder $query, string $tahun): Builder
    {
        $sqlSelect = <<<'SQL'
            anggaran_bidang.id, anggaran_bidang.anggaran_id, anggaran_bidang.bidang_id, 
            SQL;

        return $query;
    }

    public function anggaran(): BelongsTo
    {
        return $this->belongsTo(Anggaran::class, 'anggaran_id', 'id');
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id', 'id');
    }

    public function pemakaian(): HasMany
    {
        return $this->hasMany(PemakaianAnggaran::class, 'anggaran_bidang_id', 'id');
    }

    public function detailPemakaian(): HasManyThrough
    {
        return $this->hasManyThrough(PemakaianAnggaranDetail::class, PemakaianAnggaran::class, 'anggaran_bidang_id', 'pemakaian_anggaran_id');
    }
}
