<?php

namespace App\Models\Aplikasi;

use App\Database\Eloquent\Model;
use App\Models\Kepegawaian\Dokter;
use App\Models\Perawatan\Poliklinik;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SetPintuSmc extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'set_pintu_smc';

    protected $primaryKey = false;

    protected $keyType = false;

    public $incrementing = false;

    public $timestamps = false;

    /**
     * Allow mass assignment for these columns when creating mappings.
     */
    protected $fillable = [
        'kd_pintu',
        'kd_poli',
        'kd_dokter',
    ];

    public function pintu(): BelongsTo
    {
        return $this->belongsTo(Pintu::class, 'kd_pintu', 'kd_pintu');
    }

    public function poliklinik(): BelongsTo
    {
        return $this->belongsTo(Poliklinik::class, 'kd_poli', 'kd_poli');
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

    public function scopeDataPintu(Builder $query): Builder
    {
        $this->addSearchConditions([
            'set_pintu_smc.kd_pintu',
            'pintu_smc.nm_pintu',
            'set_pintu_smc.kd_poli',
            'poliklinik.nm_poli',
            'set_pintu_smc.kd_dokter',
            'dokter.nm_dokter',
        ]);

        $sqlSelect = <<<'SQL'
            set_pintu_smc.kd_pintu,
            pintu_smc.nm_pintu,
            set_pintu_smc.kd_poli,
            poliklinik.nm_poli,
            set_pintu_smc.kd_dokter,
            dokter.nm_dokter
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('pintu_smc', 'set_pintu_smc.kd_pintu', '=', 'pintu_smc.kd_pintu')
            ->join('poliklinik', 'set_pintu_smc.kd_poli', '=', 'poliklinik.kd_poli')
            ->join('dokter', 'set_pintu_smc.kd_dokter', '=', 'dokter.kd_dokter');
    }
}
