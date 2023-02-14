<?php

namespace App\Models\Keuangan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubRekening extends Model
{
    protected $primaryKey = 'kd_rek';

    protected $keyType = 'string';

    protected $table = 'subrekening';

    public $incrementing = false;

    public $timestamps = false;
}
