<?php

namespace App\Models\Keuangan\Jurnal;

use App\Models\Keuangan\Rekening;
use App\Support\Eloquent\Concerns\Searchable;
use App\Support\Eloquent\Concerns\Sortable;
use App\Support\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JurnalDetail extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_jurnal';

    protected $keyType = 'string';

    protected $table = 'detailjurnal';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'kd_rek',
        'debet',
        'kredit',
    ];

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class, 'no_jurnal', 'no_jurnal');
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class, 'kd_rek', 'kd_rek');
    }
}
