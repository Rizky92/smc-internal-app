<?php

namespace App\Models\Keuangan\RKAT;

use App\Models\Bidang;
use App\Models\Kepegawaian\Petugas;
use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as TraitsBelongsToThrough;

class PemakaianAnggaran extends Model
{
    use Sortable, Searchable, HasFactory, TraitsBelongsToThrough;

    protected $connection = 'mysql_smc';

    protected $table = 'pemakaian_anggaran_bidang';

    protected $fillable = [
        'deskripsi',
        'nominal_pemakaian',
        'tgl_dipakai',
        'anggaran_bidang_id',
        'user_id',
    ];

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
}
