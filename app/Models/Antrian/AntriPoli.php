<?php

namespace App\Models\Antrian;

use App\Database\Eloquent\Model;
use App\Models\Kepegawaian\Dokter;
use App\Models\Perawatan\Poliklinik;
use App\Models\Perawatan\RegistrasiPasien;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AntriPoli extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'antripoli';

    protected $primaryKey = null;

    public $timestamps = false;

    protected $fillable = [
        'status',
    ];

    /**
     * @psalm-return BelongsTo<Dokter>
     */
    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

    /**
     * @psalm-return BelongsTo<Poliklinik>
     */
    public function poliklinik(): BelongsTo
    {
        return $this->belongsTo(Poliklinik::class, 'kd_poli', 'kd_poli');
    }

    /**
     * @psalm-return BelongsTo<RegistrasiPasien>
     */
    public function registrasi(): BelongsTo
    {
        return $this->belongsTo(RegistrasiPasien::class, 'no_rawat', 'no_rawat');
    }
}
