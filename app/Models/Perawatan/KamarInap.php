<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;
use App\Models\Kepegawaian\Dokter;
use App\Models\Keuangan\BayarPiutang;
use App\Models\Keuangan\Billing;
use App\Models\Keuangan\NotaRanap;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class KamarInap extends Model
{
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
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function diagnosa(): HasMany
    {
        return $this->hasMany(DiagnosaPasien::class, 'no_rawat', 'no_rawat')
            ->ranap();
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

    public function scopePiutangRanap(
        Builder $query,
        string $tglAwal = '',
        string $tglAkhir = '',
        string $status = '',
        string $jenisBayar = ''
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $sqlSelect = <<<'SQL'
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
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['uangmuka' => 'float', 'totalpiutang' => 'float'])
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
            ->when(! empty($status), fn (Builder $q): Builder => $q->where('piutang_pasien.status', $status))
            ->where('reg_periksa.kd_pj', $jenisBayar);
    }

    public function scopeItemFakturPajak(Builder $query): Builder
    {
        $sqlSelect = <<<'SQL'
            kamar_inap.no_rawat,
            '080' as kode_transaksi,
            'B' as jenis_barang_jasa,
            '250100' as kode_barang_jasa,
            concat(kamar_inap.kd_kamar, ' ', bangsal.nm_bangsal) as nama_barang_jasa,
            'HARI' as nama_satuan_ukur,
            kamar_inap.trf_kamar as harga_satuan,
            sum(kamar_inap.lama) as jumlah_barang_jasa,
            0 as diskon_persen,
            0 as diskon_nominal,
            sum(kamar_inap.ttl_biaya) as dpp,
            12 as ppn_persen,
            0 as ppn_nominal,
            kamar_inap.kd_kamar as kd_jenis_prw,
            'Kamar Inap' as kategori,
            2 as urutan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('kamar', 'kamar_inap.kd_kamar', '=', 'kamar.kd_kamar')
            ->join('bangsal', 'kamar.kd_bangsal', '=', 'bangsal.kd_bangsal')
            ->whereExists(fn ($q) => $q->from('regist_faktur')->whereColumn('regist_faktur.no_rawat', 'kamar_inap.no_rawat'))
            ->groupBy('kamar_inap.no_rawat', 'kamar_inap.kd_kamar', 'bangsal.nm_bangsal', 'kamar_inap.trf_kamar');
    }
}
