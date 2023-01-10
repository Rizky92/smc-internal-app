<?php

namespace App\Models\Aplikasi;

use Illuminate\Database\Eloquent\Model;

class MappingAksesKhanza extends Model
{
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
}
