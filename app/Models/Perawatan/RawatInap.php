<?php

namespace App\Models\Perawatan;

use App\Models\Kepegawaian\Dokter;
use App\Models\Keuangan\BayarPiutang;
use App\Models\Keuangan\Billing;
use App\Models\Keuangan\NotaRanap;
use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RawatInap extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'kamar_inap';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'trf_kamar',
        'lama',
        'ttl_biaya',
    ];

    public function scopePiutangRanap(
        Builder $query,
        string $tglAwal = '',
        string $tglAkhir = '',
        string $status = '',
        string $jenisBayar = ''
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = "
            kamar_inap.no_rawat,
            nota_inap.no_nota,
            ifnull(rujuk_masuk.perujuk, '-') as perujuk,
            reg_periksa.no_rkm_medis,
            pasien.nm_pasien,
            timestamp(kamar_inap.tgl_keluar, kamar_inap.jam_keluar) as waktu_keluar,
            penjab.png_jawab,
            concat(kamar.kd_kamar, ' ', bangsal.nm_bangsal) as ruangan,
            piutang_pasien.uangmuka,
            piutang_pasien.totalpiutang
        ";

        return $query
            ->selectRaw($sqlSelect)
            ->join('reg_periksa', 'kamar_inap.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('rujuk_masuk', 'kamar_inap.no_rawat', '=', 'rujuk_masuk.no_rawat')
            ->leftJoin('nota_inap', 'reg_periksa.no_rawat', '=', 'nota_inap.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->join('kamar', 'kamar_inap.kd_kamar', '=', 'kamar.kd_kamar')
            ->join('bangsal', 'kamar.kd_bangsal', '=', 'bangsal.kd_bangsal')
            ->join('piutang_pasien', 'kamar_inap.no_rawat', '=', 'piutang_pasien.no_rawat')
            ->whereNotIn('kamar_inap.stts_pulang', ['-', 'Pindah Kamar'])
            ->whereBetween('kamar_inap.tgl_keluar', [$tglAwal, $tglAkhir])
            ->when(!empty($status), fn ($query) => $query->where('piutang_pasien.status', $status))
            ->where('reg_periksa.kd_pj', $jenisBayar);
    }

    public function billing(): HasMany
    {
        return $this->hasMany(Billing::class, 'no_rawat', 'no_rawat');
    }

    public function nota(): HasOne
    {
        return $this->hasOne(NotaRanap::class, 'no_rawat', 'no_rawat');
    }

    public function cicilanPiutang(): HasMany
    {
        return $this->hasMany(BayarPiutang::class, 'no_rawat', 'no_rawat');
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class, 'kd_kamar', 'kd_kamar');
    }

    public function dpjpRanap(): BelongsToMany
    {
        return $this->belongsToMany(Dokter::class, 'dpjp_ranap', 'no_rawat', 'kd_dokter', 'no_rawat', 'kd_dokter');
    }

    /**
     * @psalm-return Builder<TRelatedModel>
     */
    public function diagnosa(): Builder
    {
        return $this->hasMany(DiagnosaPasien::class, 'no_rawat', 'no_rawat')
            ->where('status', 'Ranap');
    }

    public function tindakanRanapPerawat(): HasMany
    {
        return $this->hasMany(TindakanRanapPerawat::class, 'no_rawat', 'no_rawat');
    }

    public function tindakanRanapDokter(): HasMany
    {
        return $this->hasMany(TindakanRanapDokter::class, 'no_rawat', 'no_rawat');
    }

    public function tindakanRanapDokterPerawat(): HasMany
    {
        return $this->hasMany(TindakanRanapDokterPerawat::class, 'no_rawat', 'no_rawat');
    }
}
