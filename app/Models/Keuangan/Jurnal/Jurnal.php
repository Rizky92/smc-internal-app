<?php

namespace App\Models\Keuangan\Jurnal;

use App\Database\Eloquent\Model;
use App\Models\Keuangan\PengeluaranHarian;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
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
    
    public function pengeluaranHarian(): BelongsTo
    {
        return $this->belongsTo(PengeluaranHarian::class, 'no_bukti', 'no_keluar');
    }

    public function scopeJurnalUmum(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->format('Y-m-d');
        }

        return $query
            ->with([
                'detail' => fn (HasMany $q) => $q->whereHas('rekening'),
                'detail.rekening:kd_rek,nm_rek',
            ])
            ->whereHas('detail')
            ->whereBetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir]);
    }

    public function scopeBukuBesar(Builder $query, string $tglAwal = '', string $tglAkhir = '', string $kodeRekening = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
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
            ->when(!empty($kodeRekening), fn (Builder $q) => $q->where('detailjurnal.kd_rek', $kodeRekening))
            ->whereBetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir]);
    }

    public function scopeJumlahDebetDanKreditBukuBesar(Builder $query, string $tglAwal = '', string $tglAkhir = '', string $kodeRekening = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
            ifnull(round(sum(detailjurnal.debet), 2), 0) debet,
            ifnull(round(sum(detailjurnal.kredit), 2), 0) kredit
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['debet' => 'float', 'kredit' => 'float'])
            ->join('detailjurnal', 'jurnal.no_jurnal', '=', 'detailjurnal.no_jurnal')
            ->join('rekening', 'detailjurnal.kd_rek', '=', 'rekening.kd_rek')
            ->when(!empty($kodeRekening), fn (Builder $q) => $q->where('detailjurnal.kd_rek', $kodeRekening))
            ->wherebetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir]);
    }

    public function scopeSaldoAwalBulanSebelumnya(Builder $query, string $tglSaldo = ''): Builder
    {
        $tglSaldo = carbon($tglSaldo)->subMonth()->endOfMonth();

        $sqlSelect = <<<SQL
            detailjurnal.kd_rek,
            case
                when rekening.balance = "D" then round(rekeningtahun.saldo_awal + sum(detailjurnal.debet) - sum(detailjurnal.kredit), 2)
                when rekening.balance = "K" then round(rekeningtahun.saldo_awal + sum(detailjurnal.kredit) - sum(detailjurnal.debet), 2)
            end saldo_awal
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('detailjurnal', 'jurnal.no_jurnal', '=', 'detailjurnal.no_jurnal')
            ->leftJoin('rekening', 'detailjurnal.kd_rek', '=', 'rekening.kd_rek')
            ->leftJoin('rekeningtahun', 'rekening.kd_rek', '=', 'rekeningtahun.kd_rek')
            ->where('rekeningtahun.thn', $tglSaldo->format('Y'))
            ->whereBetween('jurnal.tgl_jurnal', [$tglSaldo->startOfYear()->format('Y-m-d'), $tglSaldo->format('Y-m-d')])
            ->groupBy('detailjurnal.kd_rek');
    }

    public function scopeTrialBalancePerTanggal(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = carbon($tglAwal)->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = carbon($tglAkhir)->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
            detailjurnal.kd_rek,
            rekening.nm_rek,
            rekening.balance,
            round(sum(detailjurnal.debet), 2) total_debet,
            round(sum(detailjurnal.kredit), 2) total_kredit
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['total_debet' => 'float', 'total_kredit' => 'float'])
            ->join('detailjurnal', 'jurnal.no_jurnal', '=', 'detailjurnal.no_jurnal')
            ->leftJoin('rekening', 'detailjurnal.kd_rek', '=', 'rekening.kd_rek')
            ->whereBetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir])
            ->groupBy('detailjurnal.kd_rek');
    }

    public static function noJurnalBaru(Carbon $date): string
    {
        $date = $date->format('Ymd');

        $index = 1;

        $noJurnalTerakhir = static::query()
            ->where('no_jurnal', 'LIKE', "JR{$date}%")
            ->orderBy('no_jurnal', 'desc')
            ->value('no_jurnal');
        
        if ($noJurnalTerakhir) {
            $index += Str::of($noJurnalTerakhir)->substr(-6)->toInt();
        }

        return Str::of('JR')
            ->append($date)
            ->append(Str::padLeft($index, '6', '0'))
            ->value();
    }

    /**
     * @param  "U"|"P" $jenis
     * @param  \Carbon\Carbon|\DateTime|string $waktuTransaksi
     * @param  array<array{kd_rek: string, debet: int|float, kredit: int|float}> $detail
     */
    public static function catat(string $noBukti, string $keterangan, $waktuTransaksi, array $detail, string $jenis = 'U'): void
    {
        if (!$waktuTransaksi instanceof Carbon) {
            $waktuTransaksi = carbon($waktuTransaksi);
        }

        if ($waktuTransaksi->isToday()) {
            $waktuTransaksi = now();
        }

        $noJurnal = static::noJurnalBaru($waktuTransaksi);

        $hasDetail = Arr::has($detail, ['*.kd_rek', '*.debet', '*.kredit']);

        throw_if($hasDetail, 'LogicException', 'Malformed array shape found.');

        $detail = collect($detail);

        [$debet, $kredit] = [round($detail->sum('debet'), 2), round($detail->sum('kredit'), 2)];

        throw_if($debet !== $kredit, 'App\Exceptions\InequalJournalException', $debet, $kredit, $noJurnal);

        $jurnal = static::create([
            'no_jurnal'  => $noJurnal,
            'no_bukti'   => $noBukti,
            'keterangan' => $keterangan,
            'jenis'      => $jenis,
            'tgl_jurnal' => $waktuTransaksi->format('Y-m-d'),
            'jam_jurnal' => $waktuTransaksi->format('H:i:s'),
        ]);

        $detail = $jurnal
            ->detail()
            ->createMany($detail);
    }
}
