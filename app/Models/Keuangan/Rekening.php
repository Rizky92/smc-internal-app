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

        static::addGlobalScope(function (Builder $query) {
            return $query
                ->select([
                    'rekening.kd_rek',
                    'rekening.tipe',
                    'rekening.balance',
                    'rekening.level',
                ])
                ->addSelect(DB::raw('convert(rekening.nm_rek using ascii) as nm_rek'));
        });
    }

    public function scopeSaldoAwalBulanSebelumnya(Builder $query, string $tglSaldo = ''): Builder
    {
        $query->withoutGlobalScopes();

        $tglSaldo = carbon_immutable($tglSaldo);

        $tglAwalTahun = $tglSaldo->startOfYear()->format('Y-m-d');
        $tglAkhirBulanLalu = $tglSaldo->subMonth()->endOfMonth()->format('Y-m-d');

        $sqlSelect = <<<SQL
            rekening.kd_rek,
            case
                when rekening.balance = "D" then round(rekeningtahun.saldo_awal + sum(detailjurnal.debet) - sum(detailjurnal.kredit), 2)
                when rekening.balance = "K" then round(rekeningtahun.saldo_awal + sum(detailjurnal.kredit) - sum(detailjurnal.debet), 2)
            end saldo_awal
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['saldo_awal' => 'float'])
            ->leftJoin('rekeningtahun', 'rekening.kd_rek', '=', 'rekeningtahun.kd_rek')
            ->leftJoin('detailjurnal', 'rekening.kd_rek', '=', 'detailjurnal.kd_rek')
            ->join('jurnal', 'detailjurnal.no_jurnal', '=', 'jurnal.no_jurnal')
            ->where('rekeningtahun.thn', $tglSaldo->format('Y'))
            ->whereBetween('jurnal.tgl_jurnal', [$tglAwalTahun, $tglAkhirBulanLalu])
            ->groupBy('rekening.kd_rek');
    }

    public function scopeTrialBalancePerTanggal(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        $query->withoutGlobalScopes();

        if (empty($tglAwal)) {
            $tglAwal = carbon($tglAwal)->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = carbon($tglAkhir)->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
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

    public function scopeHitungDebetKreditPerPeriode(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
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
            ->where('rekening.tipe', 'R')
            ->whereBetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir])
            ->groupBy('rekening.kd_rek');
    }
}
