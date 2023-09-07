<?php

namespace App\Models\RekamMedis;

use App\Support\Eloquent\Concerns\Searchable;
use App\Support\Eloquent\Concerns\Sortable;
use App\Support\Eloquent\Model;

class Suku extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $table = 'suku_bangsa';

    public $incrementing = true;

    public $timestamps = false;
}
