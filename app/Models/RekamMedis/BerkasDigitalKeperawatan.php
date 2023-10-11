<?php

namespace App\Models\RekamMedis;

use App\Database\Eloquent\Model;
use App\Models\Perawatan\RegistrasiPasien;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BerkasDigitalKeperawatan extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'berkas_digital_perawatan';

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $searchColumns = [
        'no_rawat',
        'kode',
        'lokasi_file',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriBerkas::class, 'kode', 'kode');
    }

    public function registrasi(): BelongsTo
    {
        return $this->belongsTo(RegistrasiPasien::class, 'no_rawat', 'no_rawat');
    }
}
