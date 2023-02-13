<?php

namespace App\Models\Keuangan;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Rekening extends Model
{
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
            rekeningtahun.saldo_awal,
            sum(detailjurnal.debet) debet,
            sum(detailjurnal.kredit) kredit,
            (
                case 
                    when upper(rekening.balance) = 'K'  then round((sum(detailjurnal.kredit) - sum(detailjurnal.debet)) + rekeningtahun.saldo_awal, 2)
                    when upper(rekening.balance) = 'D'  then round((sum(detailjurnal.debet) - sum(detailjurnal.kredit)) + rekeningtahun.saldo_awal, 2)
                end
            ) saldo_akhir
        ";

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('rekeningtahun', 'rekening.kd_rek', '=', 'rekeningtahun.kd_rek')
            ->leftJoin('detailjurnal', 'rekening.kd_rek', '=', 'detailjurnal.kd_rek')
            ->leftJoin('jurnal', 'detailjurnal.no_jurnal', '=', 'jurnal.no_jurnal')
            ->where('rekening.tipe', 'R')
            ->whereBetween('jurnal.tgl_jurnal', ["{$tahun}-01-01", "{$tahun}-12-31"])
            ->groupBy([
                'rekeningtahun.thn',
                'rekeningtahun.kd_rek',
            ]);
    }

    public function scopePerhitunganLabaRugi(Builder $query, string $tahun = '2022'): Builder
    {
        // select 
        //     rekening.*,
        //     rekeningtahun.thn,
        //     rekeningtahun.saldo_awal 
        // from rekening
        // left join rekeningtahun on rekening.kd_rek = rekeningtahun.kd_rek
        // where rekening.tipe = 'R'
        // order by rekening.kd_rek
        $sqlSelect = "
            rekeningtahun.thn,
            rekening.kd_rek,
            rekening.nm_rek,
            rekeningtahun.saldo_awal,
        ";

        return $query;
    }
}
