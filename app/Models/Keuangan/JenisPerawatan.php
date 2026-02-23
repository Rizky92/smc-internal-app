<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;
use App\Models\Perawatan\Poliklinik;
use App\Models\RekamMedis\Penjamin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JenisPerawatan extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'jns_perawatan';

    protected $primaryKey = 'kd_jenis_prw';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'kd_jenis_prw',
        'nm_perawatan',
        'kd_kategori',
        'kd_pj',
        'kd_poli',
        'material',
        'bhp',
        'tarif_tindakandr',
        'tarif_tindakanpr',
        'kso',
        'menejemen',
        'total_byrdr',
        'total_byrpr',
        'total_byrdrpr',
        'status',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriPerawatan::class, 'kd_kategori', 'kd_kategori');
    }

    public function penjamin(): BelongsTo
    {
        return $this->belongsTo(Penjamin::class, 'kd_pj', 'kd_pj');
    }

    public function poli(): BelongsTo
    {
        return $this->belongsTo(Poliklinik::class, 'kd_poli', 'kd_poli');
    }

    public function scopeTarifRalan(Builder $query): Builder
    {
        $this->addSearchConditions([
            'jns_perawatan.kd_jenis_prw',
            'jns_perawatan.nm_perawatan',
            'kategori_perawatan.nm_kategori',
            'penjab.png_jawab',
            'poliklinik.nm_poli',
        ]);

        $sqlSelect = <<<'SQL'
            jns_perawatan.kd_jenis_prw,
            jns_perawatan.nm_perawatan,
            kategori_perawatan.nm_kategori,
            jns_perawatan.material,
            jns_perawatan.bhp,
            jns_perawatan.tarif_tindakandr,
            jns_perawatan.tarif_tindakanpr,
            jns_perawatan.kso,
            jns_perawatan.menejemen,
            jns_perawatan.total_byrdr,
            jns_perawatan.total_byrpr,
            jns_perawatan.total_byrdrpr,
            penjab.png_jawab,
            poliklinik.nm_poli 
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('kategori_perawatan', 'jns_perawatan.kd_kategori', '=', 'kategori_perawatan.kd_kategori')
            ->join('penjab', 'penjab.kd_pj', '=', 'jns_perawatan.kd_pj')
            ->join('poliklinik', 'poliklinik.kd_poli', '=', 'jns_perawatan.kd_poli')
            ->where('jns_perawatan.status', '1');
    }
}
