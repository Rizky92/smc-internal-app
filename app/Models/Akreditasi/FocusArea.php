<?php

namespace App\Models\Akreditasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FocusArea extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'akreditasi_focus_areas';

    protected $fillable = ['kode', 'nama', 'deskripsi', 'urutan'];

    public function standards(): HasMany
    {
        return $this->hasMany(Standard::class, 'focus_area_id');
    }
}
