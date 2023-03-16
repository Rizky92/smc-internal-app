<?php

namespace App\Models\Aplikasi;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TemplateHakAkses extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_smc';

    protected $table = 'template_hak_akses';

    protected $fillable = [
        'nama',
    ];

    protected $searchColumns = [
        'nama',
    ];

    public function hakAkses(): BelongsToMany
    {
        return $this->belongsToMany(HakAkses::class, 'template_hak_akses_detail', 'template_hak_akses_id', 'nama_field_khanza');
    }
}
