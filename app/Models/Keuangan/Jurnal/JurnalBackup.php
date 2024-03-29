<?php

namespace App\Models\Keuangan\Jurnal;

use App\Database\Eloquent\Model;
use App\Models\Kepegawaian\Pegawai;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JurnalBackup extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'jurnal_backup';

    protected $fillable = [
        'no_jurnal',
        'tgl_jurnal_asli',
        'tgl_jurnal_diubah',
        'nip',
    ];

    protected $searchColumns = [
        'no_jurnal',
        'keterangan',
    ];

    protected $casts = [
        'waktu_jurnal_asli'   => 'datetime',
        'waktu_jurnal_diubah' => 'datetime',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nik');
    }

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class, 'no_jurnal', 'no_jurnal');
    }
}
