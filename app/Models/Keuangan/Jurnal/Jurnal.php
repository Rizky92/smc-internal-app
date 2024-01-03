<?php

namespace App\Models\Keuangan\Jurnal;

use App\Database\Eloquent\Model;
use App\Models\Keuangan\PengeluaranHarian;
use App\Models\Keuangan\PenagihanPiutang;
use App\Models\Keuangan\PenagihanPiutangDetail;
use App\Models\Keuangan\TitipFaktur;
use App\Models\Keuangan\TitipFakturDetail;
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

    public function catatanPenagihan()
    {
        $penagihanDetail = PenagihanPiutangDetail::where('no_rawat', $this->no_bukti)->first();

        if ($penagihanDetail) {
            $noTagihan = $penagihanDetail->no_tagihan;
            $penagihan = PenagihanPiutang::where('no_tagihan', $noTagihan)->first();

            return $penagihan ? $penagihan->catatan : '-';
        }

        return '-';
    }
    
    public function getCatatanPenagihanAttribute()
    {
        return $this->catatanPenagihan();
    }

    public function keteranganMedis(): string
    {
        preg_match('/NO\.FAKTUR (\w+)/', $this->keterangan, $matches);

        $medicalInvoiceNumber = $matches[1] ?? null;

        if ($medicalInvoiceNumber) {
            $titipFakturDetail = TitipFakturDetail::where('no_faktur', $medicalInvoiceNumber)->first();

            if ($titipFakturDetail) {
                $titipFaktur = TitipFaktur::where('no_tagihan', $titipFakturDetail->no_tagihan)->first();
                
                if ($titipFaktur) {

                    return $titipFaktur->keterangan;
                }
            }
        }

        return '-';
    }

    public function getKeteranganMedisAttribute(): string
    {
        return $this->keteranganMedis();
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

        $this->addSearchConditions([
            'jurnal.no_jurnal',
            'jurnal.no_bukti',
            'jurnal.keterangan',
            'detailjurnal.kd_rek',
            'rekening.nm_rek',
        ]);

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
            ->when(!empty($kodeRekening), fn (Builder $q) => $q->where('detailjurnal.kd_rek', $kodeRekening))
            ->wherebetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir]);
    }

    /**
     * @param  \DateTimeInterface|string $date
     */
    public static function noJurnalBaru($date): string
    {
        $date = carbon($date)->format('Ymd');

        $index = 1;

        $noJurnalTerakhir = static::query()
            ->whereRaw('no_jurnal like ?', ["JR{$date}%"])
            ->orderBy('no_jurnal', 'desc')
            ->value('no_jurnal');
        
        if ($noJurnalTerakhir) {
            $index += Str::of($noJurnalTerakhir)->substr(-6)->toInt();
        }

        return Str::of('JR')
            ->append($date)
            ->append(Str::padLeft((string) $index, 6, '0'))
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
