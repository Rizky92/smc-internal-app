<?php

namespace App\Models\Keuangan;

use App\Models\Perawatan\RegistrasiPasien;
use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
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

    public function registrasi(): BelongsTo
    {
        return $this->belongsTo(RegistrasiPasien::class, 'no_rawat', 'no_rawat');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(BayarPiutang::class, 'no_rawat', 'no_rawat');
    }

    public function scopeTagihanPiutangAging(
        Builder $query,
        string $tglAwal = '',
        string $tglAkhir = '',
        string $jaminanPasien = '-',
        string $jenisPerawatan = 'semua',
        bool $belumLunas = false
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
            detail_penagihan_piutang.no_tagihan,
            detail_penagihan_piutang.no_rawat,
            penagihan_piutang.tanggal tgl_tagihan,
            penagihan_piutang.tanggaltempo tgl_jatuh_tempo,
            bayar_piutang.tgl_bayar,
            reg_periksa.no_rkm_medis,
            pasien.nm_pasien,
            penagihan_piutang.kd_pj,
            penjab_pasien.png_jawab penjab_pasien,
            penjab_tagihan.png_jawab penjab_piutang,
            penagihan_piutang.catatan,
            piutang_pasien.status,
            detail_piutang_pasien.nama_bayar,
            round(detail_piutang_pasien.totalpiutang, 2) total_piutang,
            round(bayar_piutang.besar_cicilan, 2) besar_cicilan,
            round(detail_piutang_pasien.totalpiutang - ifnull(bayar_piutang.besar_cicilan, 0), 2) sisa_piutang,
            datediff(?, penagihan_piutang.tanggal) umur_hari
        SQL;

        $sqlFilterOnlyPaid = DB::connection('mysql_sik')
            ->table('detail_piutang_pasien')
            ->select('detail_piutang_pasien.no_rawat')
            ->join('akun_piutang', 'detail_piutang_pasien.nama_bayar', '=', 'akun_piutang.nama_bayar')
            ->join('bayar_piutang', fn (JoinClause $join) => $join
                ->on('detail_piutang_pasien.no_rawat', '=', 'bayar_piutang.no_rawat')
                ->on('akun_piutang.kd_rek', '=', 'bayar_piutang.kd_rek_kontra'))
            ->groupBy('detail_piutang_pasien.no_rawat')
            ->havingRaw('round(sum(detail_piutang_pasien.totalpiutang - bayar_piutang.besar_cicilan), 2) != 0');

        return $query
            ->selectRaw($sqlSelect, [$tglAkhir])
            ->join('penagihan_piutang', 'detail_penagihan_piutang.no_tagihan', '=', 'penagihan_piutang.no_tagihan')
            ->leftJoin('detail_piutang_pasien', fn (JoinClause $join) => $join
                ->on('detail_penagihan_piutang.no_rawat', '=', 'detail_piutang_pasien.no_rawat')
                ->on('penagihan_piutang.kd_pj', '=', 'detail_piutang_pasien.kd_pj'))
            ->join('piutang_pasien', 'detail_piutang_pasien.no_rawat', '=', 'piutang_pasien.no_rawat')
            ->leftJoin('akun_piutang', 'detail_piutang_pasien.nama_bayar', '=', 'akun_piutang.nama_bayar')
            ->leftJoin('bayar_piutang', fn (JoinClause $join) => $join
                ->on('detail_penagihan_piutang.no_rawat', '=', 'bayar_piutang.no_rawat')
                ->on('akun_piutang.kd_rek', '=', 'bayar_piutang.kd_rek_kontra'))
            ->join('reg_periksa', 'detail_penagihan_piutang.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join(DB::raw('penjab penjab_tagihan'), 'penagihan_piutang.kd_pj', '=', 'penjab_tagihan.kd_pj')
            ->join(DB::raw('penjab penjab_pasien'), 'reg_periksa.kd_pj', '=', 'penjab_pasien.kd_pj')
            ->when($belumLunas, fn (Builder $q): Builder => $q->where('piutang_pasien.status', '!=', 'Lunas'))
            ->when($jaminanPasien !== '-', fn (Builder $q): Builder => $q->where('reg_periksa.kd_pj', $jaminanPasien))
            ->when($jenisPerawatan !== 'semua', fn (Builder $q): Builder => $q->where('reg_periksa.status_lanjut', $jenisPerawatan))
            ->where(fn (Builder $q): Builder => $q->whereNull('bayar_piutang.no_rawat')->orWhereIn('detail_penagihan_piutang.no_rawat', $sqlFilterOnlyPaid))
            ->whereBetween('penagihan_piutang.tanggal', [$tglAwal, $tglAkhir])
            ->orderByRaw('datediff(?, penagihan_piutang.tanggal) desc', [$tglAkhir])
            ->orderBy('detail_penagihan_piutang.no_rawat', 'asc')
            ->orderBy('detail_penagihan_piutang.no_tagihan', 'asc');
    }

    public function scopeTotalTagihanPiutangAging(
        Builder $query,
        string $tglAwal = '',
        string $tglAkhir = '',
        string $jaminanPasien = '-',
        string $jenisPerawatan = 'semua',
        bool $belumLunas = false
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
            case
                when datediff(?, penagihan_piutang.tanggal) <= 30 then 'periode_0_30'
                when datediff(?, penagihan_piutang.tanggal) between 31 and 60 then 'periode_31_60'
                when datediff(?, penagihan_piutang.tanggal) between 61 and 90 then 'periode_61_90'
                when datediff(?, penagihan_piutang.tanggal) > 90 then 'periode_90_up'
            end periode,
            round(sum(detail_piutang_pasien.totalpiutang), 2) total_piutang,
            round(sum(bayar_piutang.besar_cicilan), 2) total_cicilan,
            round(sum(detail_piutang_pasien.totalpiutang - ifnull(bayar_piutang.besar_cicilan, 0)), 2) sisa_piutang
        SQL;

        $sqlGroupBy = <<<SQL
            datediff(?, penagihan_piutang.tanggal) <= 30,
            datediff(?, penagihan_piutang.tanggal) between 31 and 60,
            datediff(?, penagihan_piutang.tanggal) between 61 and 90,
            datediff(?, penagihan_piutang.tanggal) > 90
        SQL;

        $sqlFilterOnlyPaid = DB::connection('mysql_sik')
            ->table('detail_piutang_pasien')
            ->select('detail_piutang_pasien.no_rawat')
            ->join('akun_piutang', 'detail_piutang_pasien.nama_bayar', '=', 'akun_piutang.nama_bayar')
            ->join('bayar_piutang', fn (JoinClause $join) => $join
                ->on('detail_piutang_pasien.no_rawat', '=', 'bayar_piutang.no_rawat')
                ->on('akun_piutang.kd_rek', '=', 'bayar_piutang.kd_rek_kontra'))
            ->groupBy('detail_piutang_pasien.no_rawat')
            ->havingRaw('round(sum(detail_piutang_pasien.totalpiutang - bayar_piutang.besar_cicilan), 2) != 0');

        return $query
            ->selectRaw($sqlSelect, [$tglAkhir, $tglAkhir, $tglAkhir, $tglAkhir])
            ->join('penagihan_piutang', 'detail_penagihan_piutang.no_tagihan', '=', 'penagihan_piutang.no_tagihan')
            ->leftJoin('detail_piutang_pasien', fn (JoinClause $join) => $join
                ->on('detail_penagihan_piutang.no_rawat', '=', 'detail_piutang_pasien.no_rawat')
                ->on('penagihan_piutang.kd_pj', '=', 'detail_piutang_pasien.kd_pj'))
            ->join('piutang_pasien', 'detail_piutang_pasien.no_rawat', '=', 'piutang_pasien.no_rawat')
            ->leftJoin('akun_piutang', 'detail_piutang_pasien.nama_bayar', '=', 'akun_piutang.nama_bayar')
            ->leftJoin('bayar_piutang', fn (JoinClause $join) => $join
                ->on('detail_penagihan_piutang.no_rawat', '=', 'bayar_piutang.no_rawat')
                ->on('akun_piutang.kd_rek', '=', 'bayar_piutang.kd_rek_kontra'))
            ->join('reg_periksa', 'detail_penagihan_piutang.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join(DB::raw('penjab penjab_tagihan'), 'penagihan_piutang.kd_pj', '=', 'penjab_tagihan.kd_pj')
            ->join(DB::raw('penjab penjab_pasien'), 'reg_periksa.kd_pj', '=', 'penjab_pasien.kd_pj')
            ->when($belumLunas, fn (Builder $q): Builder => $q->where('piutang_pasien.status', '!=', 'Lunas'))
            ->when($jaminanPasien !== '-', fn (Builder $q): Builder => $q->where('reg_periksa.kd_pj', $jaminanPasien))
            ->when($jenisPerawatan !== 'semua', fn (Builder $q): Builder => $q->where('reg_periksa.status_lanjut', $jenisPerawatan))
            ->where(fn (Builder $q): Builder => $q->whereNull('bayar_piutang.no_rawat')->orWhereIn('detail_penagihan_piutang.no_rawat', $sqlFilterOnlyPaid))
            ->whereBetween('penagihan_piutang.tanggal', [$tglAwal, $tglAkhir])
            ->groupByRaw($sqlGroupBy, [$tglAkhir, $tglAkhir, $tglAkhir, $tglAkhir]);
    }
}
