<?php

namespace App\Models\Farmasi;

use Illuminate\Database\Eloquent\Model;

class PenerimaanObatDetail extends Model
{
    protected $connection = 'mysql_sik';
    
    protected $primaryKey = false;

    protected $keyType = null;

    protected $table = 'detailpesan';

    public $incrementing = false;

    public $timestamps = false;
}
