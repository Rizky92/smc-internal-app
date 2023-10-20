<?php

namespace App\Models\Aplikasi;

use App\Database\Eloquent\Concerns\Searchable;
use App\Database\Eloquent\Concerns\Sortable;
use App\Database\Eloquent\Model;

class TemplateHakAkses extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_sik';

    protected $table = 'TemplateHakAkses';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = false;

    public $timestamps = false;

    protected $perPage = 25;

    protected $fillable = [
        // 
    ];

    protected $casts = [
        // 
    ];

    protected $searchColumns = [
        // 
    ];
}
