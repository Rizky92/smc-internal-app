<?php

namespace App\Models\Quality;

use App\Database\Eloquent\Model;
use App\Models\Bidang;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndikatorJabatanUnit extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'indikator_jabatan_unit';

    protected $fillable = [
        'jabatan_id',
        'unit_id',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'unit_id');
    }
}
