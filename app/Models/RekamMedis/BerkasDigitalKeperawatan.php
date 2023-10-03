<?php

namespace App\Models\RekamMedis;

use App\Models\Perawatan\RegistrasiPasien;
use App\Database\Eloquent\Concerns\Searchable;
use App\Database\Eloquent\Concerns\Sortable;
use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BerkasDigitalKeperawatan extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_sik';

    protected $table = 'berkas_digital_perawatan';

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $perPage = 25;

    protected $fillable = [
        // 
    ];

    protected $casts = [
        // 
    ];

    protected array $searchColumns = [
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
