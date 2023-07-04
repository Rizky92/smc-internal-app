<?php

namespace App\Models;

use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Bidang extends Model
{
    use Sortable, Searchable, HasFactory, HasRecursiveRelationships;

    protected $connection = 'mysql_smc';

    protected $table = 'bidang';

    public $timestamps = false;

    protected $fillable = [
        'nama',
        'parent_id',
    ];

    public function getParentKeyName()
    {
        return 'parent_id';
    }

    public function anggaran(): HasMany
    {
        return $this->hasMany(AnggaranBidang::class, 'bidang_id', 'id');
    }

    public function mappingBangsal(): BelongsToMany
    {
        return $this->belongsToMany(Bangsal::class, 'mapping_bidang', 'kd_bangsal', 'bidang_id', 'kd_bangsal', 'id');
    }
}
