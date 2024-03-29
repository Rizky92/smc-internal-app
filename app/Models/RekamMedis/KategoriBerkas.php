<?php

namespace App\Models\RekamMedis;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriBerkas extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'master_berkas_digital';

    protected $primaryKey = 'kode';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $searchColumns = [
        'kode',
        'nama',
    ];

    public function berkas(): HasMany
    {
        return $this->hasMany(BerkasDigitalKeperawatan::class, 'kode', 'kode');
    }
}
