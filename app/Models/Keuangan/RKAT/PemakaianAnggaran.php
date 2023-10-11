<?php

namespace App\Models\Keuangan\RKAT;

use App\Database\Eloquent\Model;
use App\Models\Bidang;
use App\Models\Kepegawaian\Petugas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as BelongsToThroughTrait;

class PemakaianAnggaran extends Model
{
    use BelongsToThroughTrait;

    protected $connection = 'mysql_smc';

    protected $table = 'pemakaian_anggaran';

    protected $fillable = [
        'judul',
        'deskripsi',
        'tgl_dipakai',
        'anggaran_bidang_id',
        'user_id',
    ];

    public function detail(): HasMany
    {
        return $this->hasMany(PemakaianAnggaranDetail::class, 'pemakaian_anggaran_id', 'id');
    }

    public function anggaranBidang(): BelongsTo
    {
        return $this->belongsTo(AnggaranBidang::class, 'anggaran_bidang_id', 'id');
    }

    public function anggaran(): BelongsToThrough
    {
        return $this->belongsToThrough(Anggaran::class, AnggaranBidang::class, null, '', [AnggaranBidang::class => 'anggaran_id']);
    }

    public function bidang(): BelongsToThrough
    {
        return $this->belongsToThrough(Bidang::class, AnggaranBidang::class, null, '', [AnggaranBidang::class => 'bidang_id']);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(Petugas::class, 'user_id', 'nip');
    }

    public function scopePenggunaanRKAT(Builder $query, int $bidangId = -1): Builder
    {
        return $query
            ->with(['petugas', 'anggaranBidang', 'anggaranBidang.anggaran', 'anggaranBidang.bidang'])
            ->withSum('detail as nominal_pemakaian', 'nominal')
            ->when(
                $bidangId !== -1,
                fn (Builder $q): Builder => $q->whereHas(
                    'anggaranBidang.bidang',
                    fn (Builder $q): Builder => $q->whereId($bidangId)
                )
            );
    }
}
