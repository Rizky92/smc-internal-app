<?php

namespace App\Models\Keuangan;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PiutangPasien extends Model
{
    Use Searchable, Sortable;

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'piutang_pasien';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeRekapPiutangPasien(
        Builder $query,
        string $periodeAwal = '',
        string $periodeAkhir = '',
        string $penjamin = ''
    ): Builder {
        if (empty($periodeAwal)) {
            $periodeAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($periodeAkhir)) {
            $periodeAkhir = now()->endOfMont()->format('Y-m-d');
        }

        $sqlSelect = "
            piutang_pasien.no_rawat,
            piutang_pasien.no_rkm_medis,
            pasien.nm_pasien,
            piutang_pasien.tgl_piutang,
            piutang_pasien.status,
            piutang_pasien.totalpiutang total,
            piutang_pasien.uangmuka uang_muka,
            ifnull(sisa_piutang.sisa, 0) terbayar,
            (piutang_pasien.sisapiutang - ifnull(sisa_piutang.sisa, 0)) sisa,
            piutang_pasien.tgltempo,
            penjab.png_jawab penjamin
        ";

        $sisaPiutang = "(
            select ifnull(sum(besar_cicilan), 0) sisa, no_rawat
            from bayar_piutang
            group by no_rawat
        ) sisa_piutang";

        return $query->selectRaw($sqlSelect)
            ->join('pasien', 'piutang_pasien.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('reg_periksa', 'piutang_pasien.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->leftJoin(DB::raw($sisaPiutang), 'piutang_pasien.no_rawat', '=', 'sisa_piutang.no_rawat')
            ->whereBetween('piutang_pasien.tgl_piutang', [$periodeAwal, $periodeAkhir])
            ->when(!empty($penjamin), fn (Builder $query) => $query->where('reg_periksa.kd_pj', $penjamin));
    }
}
