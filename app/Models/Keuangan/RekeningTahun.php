<?php

namespace App\Models\Keuangan;

use App\Support\Eloquent\Concerns\Searchable;
use App\Support\Eloquent\Concerns\Sortable;
use App\Support\Eloquent\Model;

class RekeningTahun extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'rekeningtahun';

    public $incrementing = false;

    public $timestamps = false;
}
