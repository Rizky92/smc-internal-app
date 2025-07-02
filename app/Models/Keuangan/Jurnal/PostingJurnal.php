<?php

namespace App\Models\Keuangan\Jurnal;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PostingJurnal extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'posting_jurnal';

    protected $fillable = [
        'no_jurnal',
        'tgl_jurnal',
    ];

    protected $searchColumns = [
        'posting_jurnal.no_jurnal',
        'jurnal.no_bukti',
    ];

    public function jurnal(): HasOne
    {
        return $this->hasOne(Jurnal::class, 'no_jurnal', 'no_jurnal');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(JurnalDetail::class, 'no_jurnal', 'no_jurnal');
    }

    public function scopeJumlahDebetDanKreditPostingJurnal(Builder $query, string $tglAwal = '', string $tglAkhir = '', string $jenis = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->startOfMonth()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            ifnull(round(sum(detailjurnal.debet), 2), 0) debet,
            ifnull(round(sum(detailjurnal.kredit), 2), 0) kredit
            SQL;

        $this->addSearchConditions([
            'posting_jurnal.no_jurnal',
            'jurnal.no_bukti',
            'jurnal.keterangan',
            'detailjurnal.kd_rek',
            'rekening.nm_rek',
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['debet' => 'float', 'kredit' => 'float'])
            ->join('sik.jurnal', 'smc.posting_jurnal.no_jurnal', '=', 'sik.jurnal.no_jurnal')
            ->join('sik.detailjurnal', 'sik.jurnal.no_jurnal', '=', 'sik.detailjurnal.no_jurnal')
            ->join('sik.rekening', 'sik.detailjurnal.kd_rek', '=', 'sik.rekening.kd_rek')
            ->when($jenis !== '-', fn (Builder $query) => $query->where('sik.jurnal.jenis', $jenis))
            ->whereBetween('sik.jurnal.tgl_jurnal', [$tglAwal, $tglAkhir])
            ->orderBy('sik.jurnal.tgl_jurnal')
            ->orderBy('sik.jurnal.jam_jurnal')
            ->orderBy('sik.jurnal.no_jurnal');
    }
}
