<?php

namespace App\Models\Keuangan;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Rekening extends Model
{
    use Sortable, Searchable;

    protected $primaryKey = 'kd_rek';

    protected $keyType = 'string';

    protected $table = 'rekening';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeHitungDebetKreditPerPeriode(Builder $query, string $tglAwal = '', string $tglAkhir = '', $kodeRekening = null): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->format('Y-m-d');
        }

        $sqlSelect = "
            rekening.kd_rek,
            round(sum(detailjurnal.debet), 2) debet,
            round(sum(detailjurnal.kredit), 2) kredit
        ";

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('detailjurnal', 'rekening.kd_rek', '=', 'detailjurnal.kd_rek')
            ->leftJoin('jurnal', 'detailjurnal.no_jurnal', '=', 'jurnal.no_jurnal')
            ->when(
                !is_null($kodeRekening) && $kodeRekening instanceof Arrayable,
                fn (Builder $query) => $query->whereIn('rekening.kd_rek', $kodeRekening->toArray()),
                fn (Builder $query) => $query->where('tipe', 'R')
            )
            ->whereBetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir])
            ->groupBy('rekening.kd_rek');
    }
}
