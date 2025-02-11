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
        bool $tampilkanBedaJaminanPembayaran = false
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            penagihan_piutang.no_tagihan,
            detail_penagihan_piutang.no_rawat,
            penagihan_piutang.tanggal tgl_tagihan,
            penagihan_piutang.tanggaltempo tgl_jatuh_tempo,
            detail_penagihan_piutang.diskon,
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
            round(ifnull(detail_piutang_pasien.totalpiutang, detail_penagihan_piutang.sisapiutang), 2) total_piutang,
            round(ifnull(bayar_piutang.besar_cicilan, 0), 2) besar_cicilan,
            round(ifnull(detail_piutang_pasien.totalpiutang, detail_penagihan_piutang.sisapiutang) - ifnull(bayar_piutang.besar_cicilan, 0) - ifnull(bayar_piutang.diskon_piutang, 0) - ifnull(bayar_piutang.tidak_terbayar, 0), 2) sisa_piutang,
            datediff(?, penagihan_piutang.tanggal) umur_hari,
            akun_penagihan_piutang.kd_rek kd_rek_tagihan,
            akun_penagihan_piutang.nama_bank
            SQL;

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
            'akun_penagihan_piutang.nama_bank',
        ]);

        $this->addRawColumns([
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
            ->join('akun_penagihan_piutang', 'penagihan_piutang.kd_rek', '=', 'akun_penagihan_piutang.kd_rek')
            ->leftJoin('detail_piutang_pasien', fn (JoinClause $join) => $join
                ->on('detail_penagihan_piutang.no_rawat', '=', 'detail_piutang_pasien.no_rawat')
                ->on('penagihan_piutang.kd_pj', '=', 'detail_piutang_pasien.kd_pj'))
            ->leftJoin('piutang_pasien', 'detail_penagihan_piutang.no_rawat', '=', 'piutang_pasien.no_rawat')
            ->leftJoin('akun_piutang', 'detail_piutang_pasien.nama_bayar', '=', 'akun_piutang.nama_bayar')
            ->leftJoin('bayar_piutang', fn (JoinClause $join) => $join
                ->on('detail_penagihan_piutang.no_rawat', '=', 'bayar_piutang.no_rawat')
                ->on('akun_piutang.kd_rek', '=', 'bayar_piutang.kd_rek_kontra'))
            ->join('reg_periksa', 'detail_penagihan_piutang.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('penjab as penjab_tagihan', 'penagihan_piutang.kd_pj', '=', 'penjab_tagihan.kd_pj')
            ->join('penjab as penjab_pasien', 'reg_periksa.kd_pj', '=', 'penjab_pasien.kd_pj')
            ->whereBetween('penagihan_piutang.tanggal', [$tglAwal, $tglAkhir])
            ->whereRaw('detail_piutang_pasien.sisapiutang != 0')
            ->when($jaminanPasien !== '-', fn (Builder $q): Builder => $q->where('reg_periksa.kd_pj', $jaminanPasien))
            ->when($jenisPerawatan !== 'semua', fn (Builder $q): Builder => $q->where('reg_periksa.status_lanjut', $jenisPerawatan))
            ->when($tampilkanBedaJaminanPembayaran, fn (Builder $q): Builder => $q->whereRaw('reg_periksa.kd_pj != penagihan_piutang.kd_pj'))
            ->where(fn (Builder $q): Builder => $q
                ->whereNull('bayar_piutang.no_rawat')
                ->orWhereRaw('(round(ifnull(detail_piutang_pasien.totalpiutang, detail_penagihan_piutang.sisapiutang) - ifnull(bayar_piutang.besar_cicilan, 0) - ifnull(bayar_piutang.diskon_piutang, 0) - ifnull(bayar_piutang.tidak_terbayar, 0), 2)) != 0'))
            ->orderBy('penagihan_piutang.tanggal', 'desc')
            ->orderBy('detail_penagihan_piutang.no_rawat', 'asc')
            ->orderBy('detail_penagihan_piutang.no_tagihan', 'asc');
    }

    public function scopeAccountReceivableDipilih(Builder $query, array $tagihanDipilih = []): Builder
    {
        if (empty($tagihanDipilih)) {
            return $query;
        }

        $query->reorder();

        return $query
            ->orWhereIn(
                DB::raw("concat_ws('_', penagihan_piutang.no_tagihan, penagihan_piutang.kd_pj, detail_penagihan_piutang.no_rawat)"),
                array_keys($tagihanDipilih)
            )
            ->orderByFieldFirst(
                DB::raw("concat_ws('_', penagihan_piutang.no_tagihan, penagihan_piutang.kd_pj, detail_penagihan_piutang.no_rawat)"),
                array_keys($tagihanDipilih)
            )
            ->orderBy('penagihan_piutang.tanggal', 'desc')
            ->orderBy('detail_penagihan_piutang.no_rawat', 'asc')
            ->orderBy('detail_penagihan_piutang.no_tagihan', 'asc');
    }

    public function scopeTotalAccountReceivable(
        Builder $query,
        string $tglAwal = '',
        string $tglAkhir = '',
        string $jaminanPasien = '-',
        string $jenisPerawatan = 'semua'
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            case
                when datediff(?, penagihan_piutang.tanggal) <= 30 then 'periode_0_30'
                when datediff(?, penagihan_piutang.tanggal) between 31 and 60 then 'periode_31_60'
                when datediff(?, penagihan_piutang.tanggal) between 61 and 90 then 'periode_61_90'
                when datediff(?, penagihan_piutang.tanggal) > 90 then 'periode_90_up'
            end periode,
            round(sum(ifnull(detail_piutang_pasien.totalpiutang, detail_penagihan_piutang.sisapiutang)), 2) total_piutang,
            round(sum(ifnull(bayar_piutang.besar_cicilan, 0)), 2) total_cicilan,
            round(sum(ifnull(detail_piutang_pasien.totalpiutang, detail_penagihan_piutang.sisapiutang) - ifnull(bayar_piutang.besar_cicilan, 0) - ifnull(bayar_piutang.diskon_piutang, 0) - ifnull(bayar_piutang.tidak_terbayar, 0)), 2) sisa_piutang
            SQL;

        $sqlGroupBy = <<<'SQL'
            datediff(?, penagihan_piutang.tanggal) <= 30,
            datediff(?, penagihan_piutang.tanggal) between 31 and 60,
            datediff(?, penagihan_piutang.tanggal) between 61 and 90,
            datediff(?, penagihan_piutang.tanggal) > 90
        SQL;

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
                'periode'       => 'int',
                'total_piutang' => 'float',
                'total_cicilan' => 'float',
                'sisa_piutang'  => 'float',
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
            ->when($jaminanPasien !== '-', fn (Builder $q): Builder => $q->where('reg_periksa.kd_pj', $jaminanPasien))
            ->when($jenisPerawatan !== 'semua', fn (Builder $q): Builder => $q->where('reg_periksa.status_lanjut', $jenisPerawatan))
            ->where(fn (Builder $q): Builder => $q
                ->whereNull('bayar_piutang.no_rawat')
                ->orWhereRaw('(round(ifnull(detail_piutang_pasien.totalpiutang, detail_penagihan_piutang.sisapiutang) - ifnull(bayar_piutang.besar_cicilan, 0) - ifnull(bayar_piutang.diskon_piutang, 0) - ifnull(bayar_piutang.tidak_terbayar, 0), 2)) != 0'))
            ->whereBetween('penagihan_piutang.tanggal', [$tglAwal, $tglAkhir])
            ->groupByRaw($sqlGroupBy, [$tglAkhir, $tglAkhir, $tglAkhir, $tglAkhir])
            ->havingRaw('round(sum(detail_piutang_pasien.sisapiutang), 2) != 0');
    }
}
