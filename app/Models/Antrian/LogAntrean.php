<?php

namespace App\Models\Antrian;

use App\Database\Eloquent\Model;
use App\Models\Aplikasi\Pintu;
use App\Models\Kepegawaian\Dokter;
use App\Models\Perawatan\Poliklinik;
use App\Models\Perawatan\RegistrasiPasien;
use App\Models\RekamMedis\Pasien;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAntrean extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'log_antrean';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $fillable = [
        'kd_pintu',
        'kd_poli',
        'kd_dokter',
        'no_rawat',
    ];

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

    public function pintu(): BelongsTo
    {
        return $this->belongsTo(Pintu::class, 'kd_pintu', 'kd_pintu');
    }

    public function poliklinik(): BelongsTo
    {
        return $this->belongsTo(Poliklinik::class, 'kd_poli', 'kd_poli');
    }

    public function registrasi(): BelongsTo
    {
        return $this->belongsTo(RegistrasiPasien::class, 'no_rawat', 'no_rawat');
    }

}
