<?php

namespace App\Models\Farmasi\Inventaris;

use Illuminate\Database\Eloquent\Model;

class DetailPemesananObat extends Model
{
    protected $primaryKey = false;

    protected $keyType = null;

    protected $table = 'detailpesan';

    public $incrementing = false;

    public $timestamps = false;
}
