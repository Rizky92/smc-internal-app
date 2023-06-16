<?php

namespace App\Models\Keuangan\RKAT;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnggaranDetail extends Model
{
    use Sortable, Searchable, HasFactory;

    protected $connection = 'mysql_smc';

    protected $table = 'anggaran_detail';

    public function anggaran(): BelongsTo
    {
        return $this->belongsTo(Anggaran::class, 'anggaran_id', 'id');
    }

    public function pemakaian(): HasMany
    {
        return $this->hasMany(PemakaianAnggaran::class, 'anggaran_detail_id', 'id');
    }
}
