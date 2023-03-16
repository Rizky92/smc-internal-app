<?php

namespace App\Models\Keuangan\Jurnal;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jurnal extends Model
{
    use Searchable, Sortable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_jurnal';

    protected $keyType = 'string';

    protected $table = 'jurnal';

    public $incrementing = false;

    public $timestamps = false;

    public function detail(): HasMany
    {
        return $this->hasMany(JurnalDetail::class, 'no_jurnal', 'no_jurnal');
    }

    public function scopeJurnalUmum(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->format('Y-m-d');
        }

        return $query
            ->with(['detail', 'detail.rekening:kd_rek,nm_rek'])
            ->whereBetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir]);
    }

    public function scopeBukuBesar(Builder $query, string $kodeRekening, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = "
            jurnal.tgl_jurnal,
            jurnal.jam_jurnal,
            jurnal.no_jurnal,
            jurnal.no_bukti,
            jurnal.keterangan,
            detailjurnal.kd_rek,
            rekening.nm_rek,
            detailjurnal.debet,
            detailjurnal.kredit
        ";

        return $query
            ->selectRaw($sqlSelect)
            ->join('detailjurnal', 'jurnal.no_jurnal', '=', 'detailjurnal.no_jurnal')
            ->join('rekening', 'detailjurnal.kd_rek', '=', 'rekening.kd_rek')
            ->where('detailjurnal.kd_rek', $kodeRekening)
            ->whereBetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir]);
    }

    public function scopeJumlahDebetDanKreditBukuBesar(Builder $query, string $kodeRekening, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = "
            ifnull(round(sum(detailjurnal.debet), 2), 0) debet,
            ifnull(round(sum(detailjurnal.kredit), 2), 0) kredit
        ";

        return $query
            ->selectRaw($sqlSelect)
            ->join('detailjurnal', 'jurnal.no_jurnal', '=', 'detailjurnal.no_jurnal')
            ->where('detailjurnal.kd_rek', $kodeRekening)
            ->wherebetween('jurnal.tgl_jurnal', [$tglAwal, $tglAkhir]);
    }
}
