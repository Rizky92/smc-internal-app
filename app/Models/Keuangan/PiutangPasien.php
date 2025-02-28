<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\JoinClause;

class PiutangPasien extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'piutang_pasien';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['status'];

    public function detail(): HasMany
    {
        return $this->hasMany(PiutangPasienDetail::class, 'no_rawat', 'no_rawat');
    }

    public function detailPerPembayaran(): HasOne
    {
        return $this->hasOne(PiutangPasienDetail::class, 'no_rawat', 'no_rawat');
    }

    public function scopeWithDetailPiutangPer(Builder $query, ?string $namaBayar = null, ?string $kodePenjamin = null): Builder
    {
        $data = fn ($q) => $q->where([
            ['nama_bayar', '=', $namaBayar],
            ['kd_pj', '=', $kodePenjamin],
        ]);

        if (empty($namaBayar) || empty($kodePenjamin)) {
            return $query->with('detail');
        }

        return $query->with(['detail' => $data]);
    }

    public function scopePiutangBelumLunas(Builder $query, string $tglAwal = '', string $tglAkhir = '', string $penjamin = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            piutang_pasien.no_rawat,
            piutang_pasien.tgl_piutang,
            piutang_pasien.no_rkm_medis,
            pasien.nm_pasien,
            piutang_pasien.status,
            piutang_pasien.totalpiutang,
            piutang_pasien.uangmuka,
            piutang_pasien.sisapiutang,
            piutang_pasien.tgltempo,
            penjab.png_jawab
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('pasien', 'piutang_pasien.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('reg_periksa', 'piutang_pasien.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->where('piutang_pasien.status', 'Belum Lunas')
            ->when(! empty($penjamin), fn (Builder $q): Builder => $q->where('reg_periksa.kd_pj', $penjamin));
    }

    public function scopePiutangPasienSudahLunas(
        Builder $query,
        string $tglAwal = '',
        string $tglAkhir = '',
        string $penjamin = '',
        string $rekening = '112010'
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            piutang_pasien.no_rawat,
            piutang_pasien.no_rkm_medis,
            pasien.nm_pasien,
            penjab.png_jawab,
            piutang_pasien.tgl_piutang,
            piutang_pasien.totalpiutang,
            bayar_piutang.besar_cicilan,
            piutang_pasien.status,
            piutang_pasien.tgltempo,
            bayar_piutang.tgl_bayar,
            bayar_piutang.kd_rek,
            rekening.nm_rek,
            bayar_piutang.catatan
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('pasien', 'piutang_pasien.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('reg_periksa', 'piutang_pasien.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->leftJoin('bayar_piutang', fn (JoinClause $join) => $join
                ->on('piutang_pasien.no_rawat', '=', 'bayar_piutang.no_rawat')
                ->on('piutang_pasien.no_rkm_medis', '=', 'bayar_piutang.no_rkm_medis'))
            ->leftJoin('rekening', 'bayar_piutang.kd_rek', '=', 'rekening.kd_rek')
            ->where('piutang_pasien.status', 'Lunas')
            ->wherebetween('bayar_piutang.tgl_bayar', [$tglAwal, $tglAkhir])
            ->where('bayar_piutang.kd_rek', $rekening)
            ->when(! empty($penjamin), fn (Builder $q) => $q->where('reg_periksa.kd_pj', $penjamin));
    }

    public function scopeRekapPiutangPasien(
        Builder $query,
        string $tglAwal = '',
        string $tglAkhir = '',
        string $penjamin = ''
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMont()->toDateString();
        }

        $sqlSelect = <<<'SQL'
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
        SQL;

        $sisaPiutang = BayarPiutang::query()
            ->selectRaw('ifnull(sum(besar_cicilan), 0) sisa, no_rawat')
            ->groupBy('no_rawat');

        return $query->selectRaw($sqlSelect)
            ->join('pasien', 'piutang_pasien.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('reg_periksa', 'piutang_pasien.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->leftJoinSub($sisaPiutang, 'sisa_piutang', fn (JoinClause $join) => $join->on('piutang_pasien.no_rawat', '=', 'sisa_piutang.no_rawat'))
            ->whereBetween('piutang_pasien.tgl_piutang', [$tglAwal, $tglAkhir])
            ->when(! empty($penjamin), fn (Builder $query) => $query->where('reg_periksa.kd_pj', $penjamin));
    }
}
