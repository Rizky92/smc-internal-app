<?php

namespace App\Models\Kepegawaian;

use App\Support\Eloquent\Model;

class Dokter extends Model
{
    protected $primaryKey = 'kd_dokter';

    protected $keyType = 'string';

    protected $table = 'dokter';

    public $incrementing = false;

    public $timestamps = false;
}
