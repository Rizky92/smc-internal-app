<?php

namespace App\Models\Keuangan\Jurnal;

use App\Database\Eloquent\Model;
use App\Models\Keuangan\PenagihanPiutangDetail;
use App\Models\Keuangan\PengeluaranHarian;
use App\Models\Keuangan\PiutangDilunaskan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    /**
     * @param  \DateTimeInterface|string  $date
     */
    public static function noJurnalBaru($date): string
    {
        $date = carbon($date)->format('Ymd');

        $index = 1;

        $noJurnalTerakhir = static::query()
            ->whereRaw('no_jurnal like ?', [str($date)->wrap('JR', '%')->value()])
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
     * @param  "U"|"P"  $jenis
     * @param  Carbon|\DateTime|string  $waktuTransaksi
     * @param  array<array{kd_rek: string, debet: int|float, kredit: int|float}>  $detail
     * @return static
     */
    public static function catat(string $noBukti, string $keterangan, $waktuTransaksi, array $detail, string $jenis = 'U'): ?self
    {
        if (! $waktuTransaksi instanceof Carbon) {
            $waktuTransaksi = carbon($waktuTransaksi);
        }

        if ($waktuTransaksi->isToday()) {
            $waktuTransaksi = now();
        }

        $noJurnal = static::noJurnalBaru($waktuTransaksi);

        $detail = collect($detail);

        [$debet, $kredit] = [round($detail->sum('debet'), 2), round($detail->sum('kredit'), 2)];

        if ($debet < 0 || $kredit < 0) {
            throw new \Exception('Debet dan Kredit tidak sama..!!');
        }

        throw_if($debet !== $kredit, 'App\Exceptions\InequalJournalException', $debet, $kredit, $noJurnal);

        $jurnal = static::create([
            'no_jurnal'  => $noJurnal,
            'no_bukti'   => $noBukti,
            'keterangan' => $keterangan,
            'jenis'      => $jenis,
            'tgl_jurnal' => $waktuTransaksi->toDateString(),
            'jam_jurnal' => $waktuTransaksi->format('H:i:s'),
        ]);

        $detail = $jurnal
            ->detail()
            ->createMany($detail);

        return $jurnal->load('detail');
    }
}
