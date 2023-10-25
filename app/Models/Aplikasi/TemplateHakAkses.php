<?php

namespace App\Models\Aplikasi;

use App\Database\Eloquent\Model;

class TemplateHakAkses extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'template_hak_akses';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = false;

    public $timestamps = false;
}
