<?php

namespace App\Models\Laboratorium;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Model;

class TindakanLab extends Model
{
    use Searchable, Sortable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_jenis_prw';

    protected $keyType = 'string';

    protected $table = 'jns_perawatan_lab';

    public $incrementing = false;

    public $timestamps = false;
}
