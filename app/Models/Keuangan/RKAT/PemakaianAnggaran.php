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

    public function scopePenggunaanRKAT(Builder $query, int $bidangId = -1, string $tahun = '', string $search = ''): Builder
    {
        $userIds = Petugas::on('mysql_sik')
            ->where(function ($query) use ($search) {
                $query->where('nip', 'LIKE', "%{$search}%")
                    ->orWhere('nama', 'LIKE', "%{$search}%");
            })
            ->pluck('nip')
            ->toArray();

        return $query
            ->with(['petugas', 'anggaranBidang', 'anggaranBidang.anggaran', 'anggaranBidang.bidang'])
            ->withSum('detail as nominal_pemakaian', 'nominal')
            ->when(
                $bidangId !== -1,
                fn (Builder $q): Builder => $q->whereHas(
                    'anggaranBidang.bidang',
                    fn (Builder $q) => $q->where('id', $bidangId)->orWhere('parent_id', $bidangId)
                )
            )
            ->when(
                ! empty($tahun),
                fn (Builder $q): Builder => $q->whereHas(
                    'anggaranBidang',
                    fn (Builder $q): Builder => $q->where('tahun', $tahun)
                )
            )
            ->when(
                ! empty($search),
                function (Builder $q) use ($search, $userIds) {
                    return $q
                        ->where('judul', 'LIKE', "%{$search}%")
                        ->orWhere('deskripsi', 'LIKE', "%{$search}%")
                        ->orWhere('tgl_dipakai', 'LIKE', "%{$search}%")
                        ->orWhereHas('anggaranBidang', function (Builder $q) use ($search) {
                            $q->where('tahun', 'LIKE', "%{$search}%");
                        })
                        ->orWhereHas('anggaranBidang.anggaran', function (Builder $q) use ($search) {
                            $q->where('nama', 'LIKE', "%{$search}%");
                        })
                        ->orWhereHas('anggaranBidang.bidang', function (Builder $q) use ($search) {
                            $q->where('nama', 'LIKE', "%{$search}%");
                        })
                        ->orWhereIn('user_id', $userIds);
                }
            );
    }
}
