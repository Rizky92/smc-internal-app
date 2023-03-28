<?php

namespace App\Models\Perawatan;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Model;

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
