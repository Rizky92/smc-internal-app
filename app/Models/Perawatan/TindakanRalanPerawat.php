<?php

namespace App\Models\Perawatan;

use App\Support\Eloquent\Concerns\Searchable;
use App\Support\Eloquent\Concerns\Sortable;
use App\Support\Eloquent\Model;

class TindakanRalanPerawat extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'rawat_jl_pr';

    public $incrementing = false;

    public $timestamps = false;
}
