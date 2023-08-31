<?php

namespace App\Models\Keuangan\Jurnal;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LogicException;

class Jurnal extends Model
{
    use Searchable, Sortable;

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

    public function detail(): HasMany
    {
        return $this->hasMany(JurnalDetail::class, 'no_jurnal', 'no_jurnal');
    }

    /**
     * @return static
     */
    public static function findLatest(string $tglJurnal, array $columns = ['*']): ?self
    {
        return (new static)::query()
            ->where('tgl_jurnal', $tglJurnal)
            ->orderBy('jam_jurnal', 'desc')
            ->orderBy('no_jurnal', 'desc')
            ->first($columns);
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
            ->join('detailjurnal', 'jurnal.no_jurnal', '=', 'detailjurnal.no_jurnal')
            ->join('rekening', 'detailjurnal.kd_rek', '=', 'rekening.kd_rek')
            ->when(!empty($kodeRekening), fn (Builder $q) => $q->where('detailjurnal.kd_rek', $kodeRekening))
            ->wherebetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir]);
    }

    public function noJurnalBaru(Carbon $date): string
    {
        $noJurnal = $this->newQuery()
            ->where('tgl_jurnal', $date->format('Y-m-d'))
            ->orderBy('tgl_jurnal', 'desc')
            ->orderBy('no_jurnal', 'desc')
            ->limit(1)
            ->value('no_jurnal');

        if (! $noJurnal) {
            $noJurnal = "JR";
            $noJurnal .= $date->format('Ymd');
            $noJurnal .= "000000";
        }

        $noJurnal = str($noJurnal);

        $tglJurnal = $noJurnal
            ->substr(0, 10)
            ->value();

        $indexJurnal = $noJurnal
            ->substr(-6)
            ->toInt();

        $indexJurnal += 1;

        $noJurnalBaru = str($indexJurnal)
            ->padLeft(6, '0')
            ->prepend($tglJurnal)
            ->value();

        return $noJurnalBaru;
    }

    /**
     * @param  "U"|"P" $jenis
     * @param  \Carbon\Carbon|\DateTime|string $waktuTransaksi
     * @param  array<array{kd_rek: string, debet: int|float, kredit: int|float}> $detail
     */
    public static function catat(string $noBukti, string $jenis, string $keterangan, $waktuTransaksi, array $detail): void
    {
        if (! $waktuTransaksi instanceof Carbon) {
            $waktuTransaksi = carbon($waktuTransaksi);
        }

        if ($waktuTransaksi->isToday()) {
            $waktuTransaksi = now();
        }
        
        $noJurnal = (new static)->noJurnalBaru($waktuTransaksi);

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
