<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;
use App\Models\Bangsal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kamar extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_kamar';

    protected $keyType = 'string';

    protected $table = 'kamar';

    public $incrementing = false;

    public $timestamps = false;

    public function bangsal(): BelongsTo
    {
        return $this->belongsTo(Bangsal::class, 'kd_bangsal', 'kd_bangsal');
    }

    public function rawatInap(): BelongsToMany
    {
        return $this->belongsToMany(RegistrasiPasien::class, 'kamar_inap', 'kd_kamar', 'no_rawat');
    }

    /**
     * @psalm-return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function scopeInformasiKamar(Builder $query): Builder
    {
        $sqlSelect = <<<SQL
        kamar.kd_kamar,
        kamar.trf_kamar,
        kamar.kelas,
        kamar.statusdata,
        bangsal.nm_bangsal,
        bangsal.status
        SQL;

        $this->addSearchConditions([
            'kamar.kd_kamar',
            'kamar.trf_kamar',
            'kamar.status',
            'kamar.kelas',
            'kamar.statusdata',
            'bangsal.nm_bangsal',
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->join('bangsal', 'bangsal.kd_bangsal', '=', 'kamar.kd_bangsal');
    }
}
