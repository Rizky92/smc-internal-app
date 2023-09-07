<?php

namespace App\Models\RekamMedis;

use App\Support\Eloquent\Concerns\Searchable;
use App\Support\Eloquent\Concerns\Sortable;
use App\Support\Eloquent\Model;

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
