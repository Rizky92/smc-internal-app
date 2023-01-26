<?php

namespace App\Models\Aplikasi;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Model;

class MappingAksesKhanza extends Model
{
    use Sortable, Searchable;
    
    protected $connection = 'mysql_smc';

    protected $primaryKey = 'nama_field';

    protected $keyType = 'string';

    protected $table = 'khanza_mapping_akses';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'nama_field',
        'judul_menu',
    ];

    protected $searchColumns = [
        'nama_field',
        'judul_menu',
    ];
}
