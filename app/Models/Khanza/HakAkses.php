<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class HakAkses extends Model
{
    protected $connection = 'mysql_smc';

    protected $primaryKey = 'nama_field';

    protected $keyType = 'string';

    protected $table = 'khanza_mapping_akses';

    public $incrementing = false;

    public $timestamps = false;
}
