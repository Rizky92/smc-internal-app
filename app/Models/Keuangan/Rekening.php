<?php

namespace App\Models\Keuangan;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
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

    public function scopePerhitunganLabaRugiTahunan(Builder $query, string $tahun = '2022'): Builder
    {
        $sqlSelect = "
            rekening.kd_rek,
            rekening.nm_rek,
            rekening.tipe,
            rekening.balance,
            rekeningtahun.thn,
            ifnull(rekeningtahun.saldo_awal, 0) saldo_awal,
            round(sum(detailjurnal.debet), 2) debet,
            round(sum(detailjurnal.kredit), 2) kredit,
            (
                case 
                    when upper(rekening.balance) = 'K'  then round((sum(detailjurnal.kredit) - sum(detailjurnal.debet)) + ifnull(rekeningtahun.saldo_awal, 0), 2)
                    when upper(rekening.balance) = 'D'  then round((sum(detailjurnal.debet) - sum(detailjurnal.kredit)) + ifnull(rekeningtahun.saldo_awal, 0), 2)
                end
            ) saldo_akhir
        ";

        return $query
            ->selectRaw($sqlSelect)
            ->join('rekeningtahun', 'rekening.kd_rek', '=', 'rekeningtahun.kd_rek')
            ->leftJoin('detailjurnal', 'rekening.kd_rek', '=', 'detailjurnal.kd_rek')
            ->leftJoin('jurnal', 'detailjurnal.no_jurnal', '=', 'jurnal.no_jurnal')
            ->where('rekening.tipe', 'R')
            ->whereBetween('jurnal.tgl_jurnal', ["{$tahun}-01-01", "{$tahun}-12-31"])
            ->groupBy([
                'rekeningtahun.thn',
                'rekeningtahun.kd_rek',
            ]);
    }
}
