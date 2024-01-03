<?php

namespace App\Models;

use App\Database\Eloquent\Model;
use App\Models\Perawatan\Kamar;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Bangsal extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_bangsal';

    protected $keyType = 'string';

    protected $table = 'bangsal';

    public $incrementing = false;

    public $timestamps = false;

    public function mappingBidang(): BelongsToMany
    {
        return $this->belongsToMany(Bidang::class, 'mapping_bidang', 'bidang_id', 'kd_bangsal', 'id', 'kd_bangsal');
    }

    /**
     * @psalm-return HasMany<Kamar>
     */
    public function kamar(): HasMany
    {
        return $this->hasMany(Kamar::class, 'kd_bangsal', 'kd_bangsal');
    }

    public function countEmptyRooms($class = null): int
    {
        $query = $this->kamar()->where('statusdata', '1')->where('status', 'KOSONG');

        if ($class) {
            $query->where('kelas', $class);
        }

        return $query->count();
    }

    public function countOccupiedRooms($class = null): int
    {
        $query = $this->kamar()->where('statusdata', '1')->where('status', 'ISI');

        if ($class) {
            $query->where('kelas', $class);
        }

        return $query->count();
    }

    public function scopeActiveWithKamar($query)
    {
        $subquery = Kamar::selectRaw('bangsal.kd_bangsal, kelas, COUNT(*) as total')
            ->join('bangsal', 'bangsal.kd_bangsal', '=', 'kamar.kd_bangsal')
            ->where('kamar.statusdata', '1')
            ->groupBy('bangsal.kd_bangsal', 'kamar.kelas');

        return $query->select('bangsal.*', 'subquery.kelas')
            ->leftJoinSub($subquery, 'subquery', function ($join) {
                $join->on('bangsal.kd_bangsal', '=', 'subquery.kd_bangsal');
            })
            ->where('bangsal.status', '1')
            ->orderBy('nm_bangsal')
            ->orderBy('kelas');
    }

    public static function getKelasList(): Collection
    {
        return Kamar::select('kelas')->distinct()->pluck('kelas');
    }
}
