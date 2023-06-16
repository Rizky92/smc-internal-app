<?php

namespace App\Models\Keuangan\RKAT;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemakaianAnggaran extends Model
{
    use Sortable, Searchable, HasFactory;

    protected $connection = 'mysql_smc';

    protected $table = 'pemakaian_anggaran';

    public function subAnggaran(): BelongsTo
    {
        return $this->belongsTo(AnggaranDetail::class, 'anggaran_detail_id', 'id');
    }
}
