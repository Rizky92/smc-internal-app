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

    protected $table = 'pemakaian_anggaran_bidang';

    public function subAnggaran(): BelongsTo
    {
        return $this->belongsTo(AnggaranBidang::class, 'anggaran_bidang_id', 'id');
    }
}
