<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;
use App\Models\Kepegawaian\Petugas;
use App\Models\RekamMedis\Penjamin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class PenagihanPiutang extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_tagihan';

    protected $keyType = 'string';

    protected $table = 'penagihan_piutang';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['status'];

    protected $searchColumns = [
        'no_tagihan',
        'nip',
        'nip_menyetujui',
        'kd_pj',
        'catatan',
        'kd_rek',
        'status',
    ];

    public function penagih(): BelongsTo
    {
        return $this->belongsTo(Petugas::class, 'nip', 'nip');
    }

    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(Petugas::class, 'nip_menyetujui', 'nip');
    }

    public function asuransi(): BelongsTo
    {
        return $this->belongsTo(Penjamin::class, 'kd_pj', 'kd_pj');
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class, 'kd_rek', 'kd_rek');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(PenagihanPiutangDetail::class, 'no_tagihan', 'no_tagihan');
    }

    public function scopeAccountReceivable(
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
            penagihan_piutang.no_tagihan,
            detail_penagihan_piutang.no_rawat,
            penagihan_piutang.tanggal tgl_tagihan,
            penagihan_piutang.tanggaltempo tgl_jatuh_tempo,
            bayar_piutang.tgl_bayar,
            reg_periksa.no_rkm_medis,
            pasien.nm_pasien,
            penagihan_piutang.kd_pj kd_pj_tagihan,
            penjab_tagihan.png_jawab penjab_tagihan,
            reg_periksa.kd_pj kd_pj_pasien,
            penjab_pasien.png_jawab penjab_pasien,
            penagihan_piutang.catatan,
            piutang_pasien.status,
            akun_piutang.kd_rek,
            akun_piutang.nama_bayar,
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
            ->havingRaw('round(sum(detail_piutang_pasien.totalpiutang - bayar_piutang.besar_cicilan - bayar_piutang.diskon_piutang - bayar_piutang.tidak_terbayar), 2) != 0');

        $this->addSearchConditions([
            'detail_penagihan_piutang.no_rawat',
            'reg_periksa.no_rkm_medis',
            'pasien.nm_pasien',
            'penjab_tagihan.png_jawab',
            'reg_periksa.kd_pj',
            'penjab_pasien.png_jawab',
            'piutang_pasien.status',
            'akun_piutang.kd_rek',
            'akun_piutang.nama_bayar',
        ]);

        $this->addSortColumns([
            'tgl_tagihan'     => 'penagihan_piutang.tanggal',
            'tgl_jatuh_tempo' => 'penagihan_piutang.tanggaltempo',
            'penjab_pasien'   => 'penjab_pasien.png_jawab',
            'penjab_piutang'  => 'penjab_tagihan.png_jawab',
            'total_piutang'   => DB::raw('round(detail_piutang_pasien.totalpiutang, 2)'),
            'besar_cicilan'   => DB::raw('round(bayar_piutang.besar_cicilan, 2)'),
            'sisa_piutang'    => DB::raw('round(detail_piutang_pasien.totalpiutang - ifnull(bayar_piutang.besar_cicilan, 0), 2)'),
        ]);

        return $query
            ->selectRaw($sqlSelect, [$tglAkhir])
            ->withCasts([
                'total_piutang'   => 'float',
                'besar_cicilan'   => 'float',
                'sisa_piutang'    => 'float',
                'umur_hari'       => 'int',
            ])
            ->join('detail_penagihan_piutang', 'penagihan_piutang.no_tagihan', '=', 'detail_penagihan_piutang.no_tagihan')
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
            ->whereBetween('penagihan_piutang.tanggal', [$tglAwal, $tglAkhir])
            ->when($belumLunas, fn (Builder $q): Builder => $q->where('piutang_pasien.status', '!=', 'Lunas'))
            ->when($jaminanPasien !== '-', fn (Builder $q): Builder => $q->where('reg_periksa.kd_pj', $jaminanPasien))
            ->when($jenisPerawatan !== 'semua', fn (Builder $q): Builder => $q->where('reg_periksa.status_lanjut', $jenisPerawatan))
            ->where(fn (Builder $q): Builder => $q
                ->whereNull('bayar_piutang.no_rawat')
                ->orWhereIn('detail_penagihan_piutang.no_rawat', $sqlFilterOnlyPaid))
            ->orderByRaw('datediff(?, penagihan_piutang.tanggal) desc', [$tglAkhir])
            ->orderBy('detail_penagihan_piutang.no_rawat', 'asc')
            ->orderBy('detail_penagihan_piutang.no_tagihan', 'asc');
    }

    public function scopeTotalAccountReceivable(
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
            ->havingRaw('round(sum(detail_piutang_pasien.totalpiutang - bayar_piutang.besar_cicilan - bayar_piutang.diskon_piutang - bayar_piutang.tidak_terbayar), 2) != 0');

        $this->addSearchConditions([
            'detail_penagihan_piutang.no_rawat',
            'reg_periksa.no_rkm_medis',
            'pasien.nm_pasien',
            'penjab_tagihan.png_jawab',
            'reg_periksa.kd_pj',
            'penjab_pasien.png_jawab',
            'piutang_pasien.status',
            'akun_piutang.kd_rek',
            'akun_piutang.nama_bayar',
        ]);

        return $query
            ->selectRaw($sqlSelect, [$tglAkhir, $tglAkhir, $tglAkhir, $tglAkhir])
            ->withCasts([
                'periode' => 'int',
                'total_piutang' => 'float',
                'total_cicilan' => 'float',
                'sisa_piutang' => 'float',
            ])
            ->join('detail_penagihan_piutang', 'penagihan_piutang.no_tagihan', '=', 'detail_penagihan_piutang.no_tagihan')
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
