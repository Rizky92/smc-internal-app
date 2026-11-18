<?php

namespace App\Models;

use App\Database\Eloquent\Model;

class ExportSession extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'export_sessions';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $fillable = [
        'session_id',
        'id_user',
        'export_name',
        'status',
    ];
}
