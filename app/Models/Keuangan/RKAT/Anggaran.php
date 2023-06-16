<?php

namespace App\Models\Keuangan\RKAT;

use App\Models\Bidang;
use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anggaran extends Model
{
    use Sortable, Searchable, HasFactory;

    protected $connection = 'mysql_smc';

    protected $table = 'anggaran';

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id', 'id');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(AnggaranDetail::class, 'anggaran_id', 'id');
    }

    public function scopeLaporanRKAT(Builder $query, string $tahun = '2023', string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfYear()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('anggaran_detail', 'anggaran.id', '=', 'anggaran_detail.anggaran_id')
            ->leftJoin('pemakaian_anggaran', 'anggaran_detail.id', '=', 'pemakaian_anggaran.anggaran_detail_id')
            ->leftJoin('bidang', 'anggaran.bidang_id', '=', 'bidang.id')
            ->where('anggaran.tahun', $tahun)
            ->whereBetween('pemakaian_anggaran.tgl_pemakaian', [$tglAwal, $tglAkhir]);
    }
}
