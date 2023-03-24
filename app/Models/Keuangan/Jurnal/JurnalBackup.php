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

    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope(function (Builder $query) {
            $db = DB::connection('mysql_sik')->getDatabaseName();

            $sqlSelect = <<<SQL
                jurnal_backup.no_jurnal,
                jurnal_backup.tgl_jurnal_diubah,
                jurnal_backup.tgl_asli,
                jurnal.keterangan,
                jurnal_backup.nip,
                pegawai.nama
            SQL;

            return $query
                ->selectRaw($sqlSelect)
                ->join(DB::raw("$db.jurnal jurnal"), 'jurnal_backup.no_jurnal', '=', 'jurnal.no_jurnal')
                ->join(DB::raw("$db.pegawai pegawai"), 'jurnal_backup.nip', '=', 'pegawai.nik');
        });
    }
}
