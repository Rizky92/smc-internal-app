<?php

namespace App\Models\Keuangan\Jurnal;

use App\Models\Kepegawaian\Pegawai;
use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

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

    protected $searchColumns = [
        'jurnal_backup.no_jurnal',
        'jurnal.keterangan',
    ];

    protected $casts = [
        'waktu_jurnal_asli' => 'datetime:Y-m-d H:i:s',
        'waktu_jurnal_diubah' => 'datetime:Y-m-d H:i:s',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nik');
    }
}
