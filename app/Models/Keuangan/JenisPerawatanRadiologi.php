<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;
use App\Models\RekamMedis\Penjamin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JenisPerawatanRadiologi extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'jns_perawatan_radiologi';

    protected $primaryKey = 'kd_jenis_prw';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'kd_jenis_prw',
        'nm_perawatan',
        'bagian_rs',
        'bhp',
        'tarif_perujuk',
        'tarif_tindakan_dokter',
        'tarif_tindakan_petugas',
        'kso',
        'menejemen',
        'total_byr',
        'kd_pj',
        'status',
        'kelas',
    ];

    /**
     * @psalm-return BelongsTo<Penjamin>
     */
    public function penjamin(): BelongsTo
    {
        return $this->belongsTo(Penjamin::class, 'kd_pj', 'kd_pj');
    }

    public function scopeTarifRadiologi(Builder $query): Builder
    {
        $this->addSearchConditions([
            'jns_perawatan_radiologi.kd_jenis_prw',
            'jns_perawatan_radiologi.nm_perawatan',
            'jns_perawatan_radiologi.kelas',
            'penjab.png_jawab',
        ]);

        $sqlSelect = <<<'SQL'
            jns_perawatan_radiologi.kd_jenis_prw,
            jns_perawatan_radiologi.nm_perawatan,
            jns_perawatan_radiologi.bagian_rs,
            jns_perawatan_radiologi.bhp,
            jns_perawatan_radiologi.tarif_perujuk,
            jns_perawatan_radiologi.tarif_tindakan_dokter,
            jns_perawatan_radiologi.tarif_tindakan_petugas,
            jns_perawatan_radiologi.kso,
            jns_perawatan_radiologi.menejemen,
            jns_perawatan_radiologi.total_byr,
            penjab.kd_pj,
            penjab.png_jawab,
            jns_perawatan_radiologi.kelas
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('penjab', 'penjab.kd_pj', '=', 'jns_perawatan_radiologi.kd_pj')
            ->where('jns_perawatan_radiologi.status', '1')
            ->orderBy('jns_perawatan_radiologi.kd_jenis_prw');
    }
}
