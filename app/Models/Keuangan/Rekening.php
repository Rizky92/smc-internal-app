<?php

namespace App\Models\Keuangan;

use App\Support\Eloquent\Concerns\Searchable;
use App\Support\Eloquent\Concerns\Sortable;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Rekening extends Model
{
    use Sortable, Searchable;

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

    public function scopeSemuaRekening(Builder $query): Builder
    {
        return $query
            ->withoutGlobalScopes()
            ->select(['kd_rek', 'nm_rek', 'balance'])
            ->where('tipe', 'R');
    }

    public function scopeHitungDebetKreditPerPeriode(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->format('Y-m-d');
        }

        $sqlSelect = "
            rekening.kd_rek,
            rekening.nm_rek,
            rekening.balance,
            round(sum(detailjurnal.debet), 2) debet,
            round(sum(detailjurnal.kredit), 2) kredit
        ";

        return $query
            ->withoutGlobalScopes()
            ->selectRaw($sqlSelect)
            ->leftJoin('detailjurnal', 'rekening.kd_rek', '=', 'detailjurnal.kd_rek')
            ->leftJoin('jurnal', 'detailjurnal.no_jurnal', '=', 'jurnal.no_jurnal')
            ->where('rekening.tipe', 'R')
            ->whereBetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir])
            ->groupBy('rekening.kd_rek');
    }
}
