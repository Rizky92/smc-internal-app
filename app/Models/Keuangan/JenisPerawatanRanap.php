<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;
use App\Models\Bangsal;
use App\Models\RekamMedis\Penjamin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JenisPerawatanRanap extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'jns_perawatan_inap';

    protected $primaryKey = 'kd_jenis_prw';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'kd_jenis_prw',
        'nm_perawatan',
        'kd_kategori',
        'material',
        'bhp',
        'tarif_tindakandr',
        'tarif_tindakanpr',
        'kso',
        'menejemen',
        'total_byrdr',
        'total_byrpr',
        'total_byrdrpr',
        'kd_pj',
        'kd_bangsal',
        'status',
        'kelas',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriPerawatan::class, 'kd_kategori', 'kd_kategori');
    }

    public function penjamin(): BelongsTo
    {
        return $this->belongsTo(Penjamin::class, 'kd_pj', 'kd_pj');
    }

    public function bangsal(): BelongsTo
    {
        return $this->belongsTo(Bangsal::class, 'kd_bangsal', 'kd_bangsal');
    }

    public function scopeTarifRanap(Builder $query): Builder
    {
        $this->addSearchConditions([
            'jns_perawatan_inap.kd_jenis_prw',
            'jns_perawatan_inap.nm_perawatan',
            'kategori_perawatan.nm_kategori',
            'jns_perawatan_inap.kelas',
            'penjab.png_jawab',
            'bangsal.nm_bangsal',
        ]);

        $sqlSelect = <<<'SQL'
            jns_perawatan_inap.kd_jenis_prw,
            jns_perawatan_inap.nm_perawatan,
            kategori_perawatan.nm_kategori,
            jns_perawatan_inap.material,
            jns_perawatan_inap.bhp,
            jns_perawatan_inap.tarif_tindakandr,
            jns_perawatan_inap.tarif_tindakanpr,
            jns_perawatan_inap.kso,
            jns_perawatan_inap.menejemen,
            jns_perawatan_inap.total_byrdr,
            jns_perawatan_inap.total_byrpr,
            jns_perawatan_inap.total_byrdrpr,
            jns_perawatan_inap.kelas,
            penjab.png_jawab,
            bangsal.nm_bangsal
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('kategori_perawatan', 'jns_perawatan_inap.kd_kategori', '=', 'kategori_perawatan.kd_kategori')
            ->join('penjab', 'penjab.kd_pj', '=', 'jns_perawatan_inap.kd_pj')
            ->join('bangsal', 'bangsal.kd_bangsal', '=', 'jns_perawatan_inap.kd_bangsal')
            ->where('jns_perawatan_inap.status', '1')
            ->orderBy('jns_perawatan_inap.kelas', 'asc');
    }
}
