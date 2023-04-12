<?php

namespace App\Models\Keuangan;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PenagihanPiutangDetail extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = null;

    protected $keyType = null;

    protected $table = 'detail_penagihan_piutang';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeTagihanPiutangAging(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
            detail_penagihan_piutang.no_tagihan,
            detail_penagihan_piutang.no_rawat,
            penagihan_piutang.tanggal tgl_tagihan,
            bayar_piutang.tgl_bayar,
            penagihan_piutang.tanggaltempo tgl_jatuh_tempo,
            reg_periksa.no_rkm_medis,
            pasien.nm_pasien,
            penjab_pasien.png_jawab penjab_pasien,
            penjab_tagihan.png_jawab penjab_piutang,
            penagihan_piutang.catatan,
            round(piutang_pasien.totalpiutang, 2) total_piutang,
            round(piutang_pasien.uangmuka, 2) uang_muka,
            round(detail_penagihan_piutang.sisapiutang, 2) sisa_piutang,
            (select round(sum(besar_cicilan), 2) from bayar_piutang where no_rawat = detail_penagihan_piutang.no_rawat and bayar_piutang.tgl_bayar <= '{$tglAkhir}') cicilan_sekarang,
            datediff('{$tglAkhir}', penagihan_piutang.tanggal) overdue
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('penagihan_piutang', 'detail_penagihan_piutang.no_tagihan', '=', 'penagihan_piutang.no_tagihan')
            ->leftJoin('piutang_pasien', 'detail_penagihan_piutang.no_rawat', 'piutang_pasien.no_rawat')
            ->leftJoin('bayar_piutang', 'detail_penagihan_piutang.no_rawat', 'bayar_piutang.no_rawat')
            ->join('reg_periksa', 'detail_penagihan_piutang.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin(DB::raw('penjab penjab_pasien'), 'reg_periksa.kd_pj', '=', 'penjab_pasien.kd_pj')
            ->leftJoin(DB::raw('penjab penjab_piutang'), 'penagihan_piutang.kd_pj', '=', 'penjab_piutang.kd_pj')
            ->whereRaw('piutang_pasien.total_piutang != detail_penagihan_piutang.sisapiutang')
            ->whereBetween('penagihan_piutang.tanggal', [$tglAwal, $tglAkhir]);
    }
}
