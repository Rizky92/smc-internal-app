<?php

namespace App\Models\Keuangan\Jurnal;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Model;

class JurnalBackup extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_smc';

    protected $table = 'jurnal_backup';

    protected $fillable = [
        'no_jurnal',
        'tgl_jurnal_asli',
        'tgl_jurnal_diubah',
        'nip',
    ];

    protected $casts = [
        'waktu_jurnal_asli' => 'datetime:Y-m-d H:i:s',
        'waktu_jurnal_diubah' => 'datetime:Y-m-d H:i:s',
    ];
}
