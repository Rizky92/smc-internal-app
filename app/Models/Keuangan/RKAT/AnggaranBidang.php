<?php

namespace App\Models\Keuangan\RKAT;

use App\Casts\Year;
use App\Models\Bidang;
use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnggaranBidang extends Model
{
    use Sortable, Searchable, HasFactory;

    protected $connection = 'mysql_smc';

    protected $table = 'anggaran_bidang';

    protected $fillable = [
        'tahun',
        'nominal_anggaran',
    ];

    protected $casts = [
        'tahun' => Year::class,
        'nominal_anggaran' => 'float',
    ];

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
}
