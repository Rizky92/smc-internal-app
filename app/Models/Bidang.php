<?php

namespace App\Models;

use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\Models\Keuangan\RKAT\PemakaianAnggaranDetail;
use App\Database\Eloquent\Concerns\Searchable;
use App\Database\Eloquent\Concerns\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Bidang extends Model
{
    use Sortable, Searchable, HasFactory, HasRelationships, HasRecursiveRelationships;

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

    public function anggaranBidang(): HasMany
    {
        return $this->hasMany(AnggaranBidang::class, 'bidang_id', 'id');
    }

    public function mappingBangsal(): BelongsToMany
    {
        return $this->belongsToMany(Bangsal::class, 'mapping_bidang', 'kd_bangsal', 'bidang_id', 'kd_bangsal', 'id');
    }

    public function totalPemakaian(): HasManyDeep
    {
        return $this->hasManyDeep(
            PemakaianAnggaranDetail::class,
            [
                AnggaranBidang::class,
                PemakaianAnggaran::class,
            ],
            [
                'bidang_id',
                'anggaran_bidang_id',
                'pemakaian_anggaran_id',
            ],
            [
                'id',
                'id',
                'id',
            ]
        );
    }
}
