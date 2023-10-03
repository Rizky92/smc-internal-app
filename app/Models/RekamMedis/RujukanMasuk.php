<?php

namespace App\Models\RekamMedis;

use App\Database\Eloquent\Concerns\Searchable;
use App\Database\Eloquent\Concerns\Sortable;
use App\Database\Eloquent\Model;

class RujukanMasuk extends Model
{
    use Searchable, Sortable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'rujuk_masuk';

    public $incrementing = false;

    public $timestamps = false;
}
