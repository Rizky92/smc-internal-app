<?php

namespace App\Models\RekamMedis;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriBerkas extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_sik';

    protected $table = 'master_berkas_digital';

    protected $primaryKey = 'kode';

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
        'kode',
        'nama',
    ];

    public function berkas(): HasMany
    {
        return $this->hasMany(BerkasDigitalKeperawatan::class, 'kode', 'kode');
    }
}
