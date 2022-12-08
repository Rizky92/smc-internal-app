<?php

namespace App\Models\Farmasi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ResepObatRacikan extends Model
{
    protected $primaryKey = null;

    protected $table = 'obat_racikan';

    public $incrementing = false;

    public $timestamps = false;
}
