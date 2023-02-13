<?php

namespace App\Models\Keuangan;

use Illuminate\Database\Eloquent\Model;

class Rekening extends Model
{
    protected $primaryKey = 'kd_rek';

    protected $keyType = 'string';

    protected $table = 'rekening';

    public $incrementing = false;

    public $timestamps = false;
}
