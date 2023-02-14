<?php

namespace App\Models\Keuangan;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RekeningTahun extends Model
{
    use Sortable, Searchable;

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'rekeningtahun';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeSaldoAwalRekening(Builder $query, string $kodeRekening, string $tahun = ''): Builder
    {
        if (empty($tahun)) {
            $tahun = now()->format('Y');
        }

        $sqlSelect = "
            rekeningtahun.saldo_awal,
            rekening.tipe, 
            rekening.balance
        ";

        return $query
            ->selectRaw($sqlSelect)
            ->join('rekening', 'rekeningtahun.kd_rek', 'rekening.kd_rek')
            ->where('rekeningtahun.kd_rek', $kodeRekening)
            ->where('rekeningtahun.thn', $tahun);
    }

    public function scopeTahun(Builder $query): Builder
    {
        return $query
            ->selectRaw('rekeningtahun.thn')
            ->groupBy('rekeningtahun.thn');
    }
}
