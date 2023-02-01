<?php

namespace App\Models\Farmasi\Inventaris;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndustriFarmasi extends Model
{
    use HasFactory;

    protected $primaryKey = 'kode_industri';

    protected $keyType = 'string';

    protected $table = 'industrifarmasi';

    public $incrementing = false;

    public $timestamps = false;
}
