<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Rekening extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_rek';

    protected $keyType = 'string';

    protected $table = 'rekening';

    public $incrementing = false;

    public $timestamps = false;

    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope(fn (Builder $query) => $query
            ->select([
                'rekening.kd_rek',
                'rekening.tipe',
                'rekening.balance',
                'rekening.level',
            ])
            ->addSelect(DB::raw('convert(rekening.nm_rek using ascii) as nm_rek')));
    }

    public function scopeSaldoAwalBulanSebelumnya(Builder $query, string $tglSaldo = ''): Builder
    {
        $query->withoutGlobalScopes();

        $tglSaldo = carbon_immutable($tglSaldo);

        $tglAwalTahun = $tglSaldo->startOfYear()->toDateString();
        $tglAkhirBulanLalu = $tglSaldo->subMonth()->endOfMonth()->toDateString();

        $sqlSelect = <<<'SQL'
            rekening.kd_rek,
            case
                when rekening.balance = "D" then round(sum(detailjurnal.debet) - sum(detailjurnal.kredit), 2)
                when rekening.balance = "K" then round(sum(detailjurnal.kredit) - sum(detailjurnal.debet), 2)
            end total_transaksi
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['saldo_awal' => 'float'])
            ->leftJoin('detailjurnal', 'rekening.kd_rek', '=', 'detailjurnal.kd_rek')
            ->leftJoin('jurnal', 'detailjurnal.no_jurnal', '=', 'jurnal.no_jurnal')
            ->whereBetween('jurnal.tgl_jurnal', [$tglAwalTahun, $tglAkhirBulanLalu])
            ->groupBy('rekening.kd_rek');
    }

    public function scopeTrialBalancePerTanggal(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        $query->withoutGlobalScopes();

        if (empty($tglAwal)) {
            $tglAwal = carbon($tglAwal)->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = carbon($tglAkhir)->toDateString();
        }

        $sqlSelect = <<<'SQL'
            detailjurnal.kd_rek,
            round(sum(detailjurnal.debet), 2) total_debet,
            round(sum(detailjurnal.kredit), 2) total_kredit
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['total_debet' => 'float', 'total_kredit' => 'float'])
            ->leftJoin('detailjurnal', 'rekening.kd_rek', '=', 'detailjurnal.kd_rek')
            ->join('jurnal', 'detailjurnal.no_jurnal', '=', 'jurnal.no_jurnal')
            ->whereBetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir])
            ->groupBy('rekening.kd_rek');
    }

    public function scopeSemuaRekening(Builder $query): Builder
    {
        return $query
            ->withoutGlobalScopes()
            ->select(['kd_rek', 'nm_rek', 'balance']);
    }

    public function scopeHitungDebetKreditPerPeriode(Builder $query, string $tglAwal = '', string $tglAkhir = '', string $kodePenjamin = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            rekening.kd_rek,
            rekening.nm_rek,
            rekening.balance,
            round(sum(detailjurnal.debet), 2) debet,
            round(sum(detailjurnal.kredit), 2) kredit
            SQL;

        return $query
            ->withoutGlobalScopes()
            ->selectRaw($sqlSelect)
            ->withCasts(['debet' => 'float', 'kredit' => 'float'])
            ->leftJoin('detailjurnal', 'rekening.kd_rek', '=', 'detailjurnal.kd_rek')
            ->leftJoin('jurnal', 'detailjurnal.no_jurnal', '=', 'jurnal.no_jurnal')
            ->leftJoin('reg_periksa', 'jurnal.no_bukti', '=', 'reg_periksa.no_rawat')
            ->when(! empty($kodePenjamin), fn (Builder $q) => $q->where('reg_periksa.kd_pj', $kodePenjamin))
            ->where('rekening.tipe', 'R')
            ->whereBetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir])
            ->groupBy('rekening.kd_rek');
    }
}
