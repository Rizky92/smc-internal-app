<?php

namespace App\Models\Keuangan\Jurnal;

use App\Database\Eloquent\Model;
use App\Exceptions\EmptyTransactionException;
use App\Exceptions\InequalJournalException;
use App\Exceptions\TransactionLessThanZeroException;
use App\Models\Keuangan\PenagihanPiutangDetail;
use App\Models\Keuangan\PengeluaranHarian;
use App\Models\Keuangan\PiutangDilunaskan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @psalm-template TDetail of \Illuminate\Support\Collection<array-key, array{kd_rek: string, debet: numeric, kredit: numeric}>|array<array-key, array{kd_rek: string, debet: numeric, kredit: numeric}>
 */
class Jurnal extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_jurnal';

    protected $keyType = 'string';

    protected $table = 'jurnal';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'no_jurnal',
        'no_bukti',
        'keterangan',
        'jenis',
        'tgl_jurnal',
        'jam_jurnal',
    ];

    protected $searchColumns = [
        'no_jurnal',
        'no_bukti',
        'keterangan',
    ];

    public function detail(): HasMany
    {
        return $this->hasMany(JurnalDetail::class, 'no_jurnal', 'no_jurnal');
    }

    public function piutangDilunaskan(): HasOne
    {
        return $this->hasOne(PiutangDilunaskan::class, 'no_jurnal', 'no_jurnal');
    }

    public function pengeluaranHarian(): BelongsTo
    {
        return $this->belongsTo(PengeluaranHarian::class, 'no_bukti', 'no_keluar');
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(PenagihanPiutangDetail::class, 'no_bukti', 'no_rawat');
    }

    public function postingJurnal(): BelongsTo
    {
        return $this->belongsTo(PostingJurnal::class, 'no_jurnal', 'no_jurnal');
    }

    public function scopeJurnalUmum(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->toDateString();
        }

        $this->addRawColumns('waktu_jurnal', DB::raw("concat(jurnal.tgl_jurnal, ' ', jurnal.jam_jurnal)"));

        return $query
            ->with([
                'detail' => fn (HasMany $q) => $q->whereHas('rekening'),
                'detail.rekening:kd_rek,nm_rek',
            ])
            ->whereHas('detail')
            ->whereBetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir]);
    }

    public function scopeJurnalPosting(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        $sik = $query->getQuery()->getConnection()->getDatabaseName();
        $smc = PostingJurnal::query()->getConnection()->getDatabaseName();

        return $query->jurnalUmum($tglAwal, $tglAkhir)
            ->whereRaw("exists(select * from $smc.posting_jurnal where $smc.posting_jurnal.no_jurnal = $sik.jurnal.no_jurnal)");
    }

    public function scopeJumlahDebetKreditJurnalPosting(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        $sik = $query->getQuery()->getConnection()->getDatabaseName();
        $smc = PostingJurnal::query()->getConnection()->getDatabaseName();

        return $query->jumlahDebetKreditBukuBesar($tglAwal, $tglAkhir)
            ->whereRaw("exists(select * from $smc.posting_jurnal where $smc.posting_jurnal.no_jurnal = $sik.jurnal.no_jurnal)");
    }

    public function scopeBukuBesar(Builder $query, string $tglAwal = '', string $tglAkhir = '', string $kodeRekening = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $this->addSearchConditions([
            'jurnal.no_jurnal',
            'jurnal.no_bukti',
            'jurnal.keterangan',
            'detailjurnal.kd_rek',
            'rekening.nm_rek',
        ]);

        $sqlSelect = <<<'SQL'
            jurnal.tgl_jurnal,
            jurnal.jam_jurnal,
            jurnal.no_jurnal,
            jurnal.no_bukti,
            jurnal.keterangan,
            detailjurnal.kd_rek,
            rekening.nm_rek,
            detailjurnal.debet,
            detailjurnal.kredit
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['debet' => 'float', 'kredit' => 'float'])
            ->join('detailjurnal', 'jurnal.no_jurnal', '=', 'detailjurnal.no_jurnal')
            ->join('rekening', 'detailjurnal.kd_rek', '=', 'rekening.kd_rek')
            ->when(! empty($kodeRekening), fn (Builder $q) => $q->where('detailjurnal.kd_rek', $kodeRekening))
            ->whereBetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir]);
    }

    public function scopeJumlahDebetKreditBukuBesar(Builder $query, string $tglAwal = '', string $tglAkhir = '', string $kodeRekening = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            ifnull(round(sum(detailjurnal.debet), 2), 0) debet, ifnull(round(sum(detailjurnal.kredit), 2), 0) kredit
            SQL;

        $this->addSearchConditions([
            'jurnal.no_jurnal',
            'jurnal.no_bukti',
            'jurnal.keterangan',
            'detailjurnal.kd_rek',
            'rekening.nm_rek',
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['debet' => 'float', 'kredit' => 'float'])
            ->join('detailjurnal', 'jurnal.no_jurnal', '=', 'detailjurnal.no_jurnal')
            ->join('rekening', 'detailjurnal.kd_rek', '=', 'rekening.kd_rek')
            ->when(! empty($kodeRekening), fn (Builder $q) => $q->where('detailjurnal.kd_rek', $kodeRekening))
            ->wherebetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir]);
    }

    public function scopeJurnalPiutangDilunaskan(Builder $query, ?string $latest = null): Builder
    {
        $latest ??= '2022-10-30 23:59:59.999';

        $sqlSelect = <<<'SQL'
            jurnal.no_jurnal,
            concat(jurnal.tgl_jurnal, ' ', jurnal.jam_jurnal) as waktu_jurnal,
            detail_penagihan_piutang.no_rawat,
            bayar_piutang.no_rkm_medis,
            penagihan_piutang.no_tagihan,
            penagihan_piutang.kd_pj as kd_pj_tagihan,
            detail_piutang_pasien.kd_pj,
            penagihan_piutang.catatan,
            detail_piutang_pasien.totalpiutang,
            bayar_piutang.besar_cicilan,
            penagihan_piutang.tanggal as tgl_tagihan,
            penagihan_piutang.tanggaltempo as tgl_jatuhtempo,
            bayar_piutang.tgl_bayar,
            bayar_piutang.kd_rek,
            rekening.nm_rek,
            bayar_piutang.kd_rek_kontra,
            penagihan_piutang.nip,
            penagihan_piutang.nip_menyetujui,
            jurnal.keterangan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('detailjurnal', 'jurnal.no_jurnal', '=', 'detailjurnal.no_jurnal')
            ->join('detail_penagihan_piutang', 'jurnal.no_bukti', '=', 'detail_penagihan_piutang.no_rawat')
            ->join('penagihan_piutang', 'detail_penagihan_piutang.no_tagihan', '=', 'penagihan_piutang.no_tagihan')
            ->join('detail_piutang_pasien', 'detail_penagihan_piutang.no_rawat', '=', 'detail_piutang_pasien.no_rawat')
            ->join('akun_piutang', 'detail_piutang_pasien.nama_bayar', '=', 'akun_piutang.nama_bayar')
            ->leftJoin('bayar_piutang', fn (JoinClause $join) => $join
                ->on('detail_penagihan_piutang.no_rawat', '=', 'bayar_piutang.no_rawat')
                ->on('akun_piutang.kd_rek', '=', 'bayar_piutang.kd_rek_kontra'))
            ->join('rekening', 'bayar_piutang.kd_rek', '=', 'rekening.kd_rek')
            ->where(fn (Builder $query) => $query
                ->where('jurnal.keterangan', 'like', 'bayar piutang% %oleh%')
                ->orWhere('jurnal.keterangan', 'like', 'bayar piutang tagihan% %oleh%')
                ->orWhere('jurnal.keterangan', 'like', 'pembatalan bayar piutang% %oleh%'))
            ->where('detailjurnal.kredit', '>', 0)
            ->whereColumn('detailjurnal.kd_rek', '=', 'akun_piutang.kd_rek')
            ->whereBetween('jurnal.tgl_jurnal', [$latest, now()])
            ->whereColumn('penagihan_piutang.kd_pj', '=', 'detail_piutang_pasien.kd_pj')
            ->whereNotIn('detail_penagihan_piutang.no_rawat', PenagihanPiutangDetail::query()->select('no_rawat')->groupBy('no_rawat')->havingRaw('count(*) > 1'))
            ->orderBy('jurnal.tgl_jurnal')
            ->orderBy('jurnal.jam_jurnal');
    }

    /**
     * @param  \DateTimeInterface|string  $date
     */
    public static function noJurnalBaru($date, int $index = 1): string
    {
        $date = carbon($date)->format('Ymd');

        $noJurnalTerakhir = static::query()
            ->where('no_jurnal', 'like', [str($date)->wrap('JR', '%')->value()])
            ->orderBy('no_jurnal', 'desc')
            ->value('no_jurnal');

        if ($noJurnalTerakhir) {
            $index += str($noJurnalTerakhir)->substr(-6)->toInt();
        }

        return str('JR')
            ->append($date)
            ->append(Str::padLeft((string) $index, 6, '0'))
            ->value();
    }

    /**
     * @param  Carbon|\DateTime|string  $waktuTransaksi
     * @param  TDetail  $detail
     * @param  "U"|"P"  $jenis
     */
    public static function catat(string $noBukti, string $keterangan, $waktuTransaksi, $detail = [], string $jenis = 'U'): self
    {
        if (! $waktuTransaksi instanceof Carbon) {
            $waktuTransaksi = carbon($waktuTransaksi);
        }

        if ($waktuTransaksi->isToday()) {
            $waktuTransaksi = now();
        }

        return static::create([
            'no_jurnal'  => static::noJurnalBaru($waktuTransaksi),
            'no_bukti'   => $noBukti,
            'keterangan' => $keterangan,
            'jenis'      => $jenis,
            'tgl_jurnal' => $waktuTransaksi->toDateString(),
            'jam_jurnal' => $waktuTransaksi->format('H:i:s'),
        ])->isiDetail($detail);
    }

    /**
     * @param  TDetail  $detail
     */
    public function isiDetail($detail = []): self
    {
        $detail = collect($detail);

        if ($detail->isEmpty()) {
            return $this;
        }

        [$debet, $kredit] = [round($detail->sum('debet')), round($detail->sum('kredit'))];

        if ($debet !== $kredit) {
            throw new InequalJournalException($debet, $kredit);
        }

        if ($debet < 0 || $kredit < 0) {
            throw new TransactionLessThanZeroException($debet, $kredit);
        }

        if ($detail->isEmpty() || ($debet === $kredit && $debet === 0.0)) {
            throw new EmptyTransactionException($debet, $kredit);
        }

        $this->detail()->createMany($detail->all());

        return $this->load('detail');
    }

    public function scopeLabaRugiRalan(Builder $query, string $tglAwal = '', string $tglAkhir = '', string $kodePenjamin = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = carbon($tglAwal)->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = carbon($tglAkhir)->toDateString();
        }

        $sqlSelect = <<<'SQL'
            poliklinik.nm_poli as unit,
            dokter.nm_dokter,
            detailjurnal.kd_rek,
            rekening.nm_rek,
            rekening.balance,
            round(sum(detailjurnal.debet), 2) as debet,
            round(sum(detailjurnal.kredit), 2) as kredit
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['debet' => 'float', 'kredit' => 'float'])
            ->join('detailjurnal', 'jurnal.no_jurnal', '=', 'detailjurnal.no_jurnal')
            ->join('rekening', 'detailjurnal.kd_rek', '=', 'rekening.kd_rek')
            ->join('reg_periksa', 'jurnal.no_bukti', '=', 'reg_periksa.no_rawat')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->join('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->whereBetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir])
            ->where('rekening.tipe', 'R')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->when(! empty($kodePenjamin), fn ($q) => $q->where('reg_periksa.kd_pj', $kodePenjamin))
            ->groupBy('reg_periksa.kd_poli', 'reg_periksa.kd_dokter', 'detailjurnal.kd_rek')
            ->orderBy('poliklinik.nm_poli')
            ->orderBy('rekening.balance')
            ->orderBy('detailjurnal.kd_rek');
    }

    public function scopeLabaRugiRanap(Builder $query, string $tglAwal = '', string $tglAkhir = '', string $kodePenjamin = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }
        if (empty($tglAkhir)) {
            $tglAkhir = now()->toDateString();
        }

        // Correlated subquery untuk unit (kelas kamar)
        $unitSub = DB::connection('mysql_sik')
            ->table('kamar_inap')
            ->select('kamar.kelas')
            ->join('kamar', 'kamar_inap.kd_kamar', '=', 'kamar.kd_kamar')
            ->whereColumn('kamar_inap.no_rawat', 'jurnal.no_bukti')
            ->whereNotIn('kamar_inap.stts_pulang', ['-', 'Pindah Kamar'])
            ->orderByDesc('kamar_inap.tgl_keluar')
            ->orderByDesc('kamar_inap.jam_keluar')
            ->limit(1);

        // Correlated subquery untuk nm_dokter (dpjp pertama)
        $dokterSub = DB::connection('mysql_sik')
            ->table('dpjp_ranap')
            ->select('dokter.nm_dokter')
            ->join('dokter', 'dpjp_ranap.kd_dokter', '=', 'dokter.kd_dokter')
            ->whereColumn('dpjp_ranap.no_rawat', 'jurnal.no_bukti')
            ->limit(1);

        // Inner subquery (alias t)
        $innerSub = DB::connection('mysql_sik')
            ->table('jurnal')
            ->selectRaw("
                ifnull(({$unitSub->toSql()}), '') as unit,
                ifnull(({$dokterSub->toSql()}), '') as nm_dokter,
                detailjurnal.kd_rek,
                rekening.nm_rek,
                rekening.balance,
                detailjurnal.debet,
                detailjurnal.kredit
            ")
            ->addBinding($unitSub->getBindings())
            ->addBinding($dokterSub->getBindings())
            ->join('detailjurnal', 'jurnal.no_jurnal', '=', 'detailjurnal.no_jurnal')
            ->join('rekening', 'detailjurnal.kd_rek', '=', 'rekening.kd_rek')
            ->join('reg_periksa', 'jurnal.no_bukti', '=', 'reg_periksa.no_rawat')
            ->whereBetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir])
            ->where('reg_periksa.status_lanjut', 'Ranap')
            ->where('rekening.tipe', 'R')
            ->when(! empty($kodePenjamin), fn ($q) => $q->where('reg_periksa.kd_pj', $kodePenjamin));

        return $query
            ->fromSub($innerSub, 't')
            ->selectRaw('t.unit, t.nm_dokter, t.kd_rek, t.nm_rek, t.balance, round(sum(t.debet), 2) as debet, round(sum(t.kredit), 2) as kredit')
            ->withCasts(['debet' => 'float', 'kredit' => 'float'])
            ->groupBy('t.unit', 't.nm_dokter', 't.kd_rek')
            ->orderBy('t.unit')
            ->orderBy('t.nm_dokter')
            ->orderBy('t.balance')
            ->orderBy('t.kd_rek');
    }

    public function scopeLabaRugi(Builder $query, string $tglAwal = '', string $tglAkhir = '', string $kodePenjamin = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            '' as unit,
            '' as nm_dokter,
            detailjurnal.kd_rek,
            rekening.nm_rek,
            rekening.balance,
            round(sum(detailjurnal.debet), 2) as debet,
            round(sum(detailjurnal.kredit), 2) as kredit
        SQL;

        if (! empty($kodePenjamin)) {
            return $query
                ->selectRaw($sqlSelect)
                ->join('detailjurnal', 'jurnal.no_jurnal', '=', 'detailjurnal.no_jurnal')
                ->join('rekening', 'detailjurnal.kd_rek', '=', 'rekening.kd_rek')
                ->whereRaw('1 = 0');
        }

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['debet' => 'float', 'kredit' => 'float'])
            ->join('detailjurnal', 'jurnal.no_jurnal', '=', 'detailjurnal.no_jurnal')
            ->join('rekening', 'detailjurnal.kd_rek', '=', 'rekening.kd_rek')
            ->whereBetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir])
            ->where('rekening.tipe', 'R')
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('reg_periksa')
                    ->whereColumn('reg_periksa.no_rawat', 'jurnal.no_bukti');
            })
            ->groupBy('detailjurnal.kd_rek')
            ->orderBy('rekening.balance')
            ->orderBy('detailjurnal.kd_rek');
    }
}
