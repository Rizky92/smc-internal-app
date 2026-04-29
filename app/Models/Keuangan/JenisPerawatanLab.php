<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;
use App\Models\RekamMedis\Penjamin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JenisPerawatanLab extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'jns_perawatan_lab';

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
        'kategori',
    ];

    public function penjamin(): BelongsTo
    {
        return $this->belongsTo(Penjamin::class, 'kd_pj', 'kd_pj');
    }

    public function scopeTarifLab(Builder $query): Builder
    {
        $this->addSearchConditions([
            'jns_perawatan_lab.kd_jenis_prw',
            'jns_perawatan_lab.nm_perawatan',
            'jns_perawatan_lab.kelas',
            'penjab.png_jawab',
        ]);

        $sqlSelect = <<<'SQL'
            jns_perawatan_lab.kd_jenis_prw,
            jns_perawatan_lab.nm_perawatan,
            jns_perawatan_lab.bagian_rs,
            jns_perawatan_lab.bhp,
            jns_perawatan_lab.tarif_perujuk,
            jns_perawatan_lab.tarif_tindakan_dokter,
            jns_perawatan_lab.tarif_tindakan_petugas,
            jns_perawatan_lab.kso,
            jns_perawatan_lab.menejemen,
            jns_perawatan_lab.total_byr,
            penjab.png_jawab,
            jns_perawatan_lab.status,
            jns_perawatan_lab.kelas,
            jns_perawatan_lab.kategori
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('penjab', 'jns_perawatan_lab.kd_pj', '=', 'penjab.kd_pj')
            ->where('jns_perawatan_lab.status', '1');
    }
}
