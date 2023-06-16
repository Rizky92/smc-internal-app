<?php

namespace App\Models;

use App\Models\Keuangan\RKAT\Anggaran;
use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bidang extends Model
{
    use Sortable, Searchable, HasFactory;

    protected $connection = 'mysql_smc';

    protected $table = 'bidang';

    public $timestamps = false;

    public function anggaran(): HasMany
    {
        return $this->hasMany(Anggaran::class, 'bidang_id', 'id');
    }
}
