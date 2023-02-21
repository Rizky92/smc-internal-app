<?php

namespace App\Models\RekamMedis;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Model;

class RujukanMasuk extends Model
{
    use Searchable, Sortable;

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'rujuk_masuk';

    public $incrementing = false;

    public $timestamps = false;
}
