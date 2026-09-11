<?php

namespace App\Models\Casemix;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class BpjsPrb extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'bpjs_prb';

    protected $primaryKey = 'no_sep';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * @psalm-return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function scopeLaporanPotensiPrb(Builder $query, string $tglAwal, string $tglAkhir): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $this->addSearchConditions([
            'bridging_sep.no_sep',
            'bridging_sep.no_rawat',
            'bridging_sep.nomr',
            'bridging_sep.no_kartu',
            'bridging_sep.nama_pasien',
            'bridging_sep.nmpolitujuan',
            'bridging_sep.nmdpdjp',
            'bridging_sep.diagawal',
            'bridging_sep.nmdiagnosaawal',
            'bpjs_prb.prb',
        ]);

        $sqlSelect = <<<'SQL'
            bridging_sep.no_sep no_sep,
            bridging_sep.tglsep tglsep,
            bridging_sep.no_rawat no_rawat,
            bridging_sep.nomr nomr,
            bridging_sep.no_kartu no_kartu,
            bridging_sep.nama_pasien nama_pasien,
            bridging_sep.nmpolitujuan nmpolitujuan,
            bridging_sep.kddpjp kddpjp,
            bridging_sep.nmdpdjp nmdpdjp,
            bridging_sep.jnspelayanan jnspelayanan,
            bridging_sep.diagawal diagawal,
            bridging_sep.nmdiagnosaawal nmdiagnosaawal,
            bridging_sep.peserta peserta,
            bridging_sep.asal_rujukan asal_rujukan,
            bridging_sep.no_rujukan no_rujukan,
            bridging_sep.tglrujukan tglrujukan,
            bridging_sep.noskdp noskdp,
            bpjs_prb.prb prb
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('bridging_sep', 'bpjs_prb.no_sep', '=', 'bridging_sep.no_sep')
            ->where('bpjs_prb.prb', 'Potensi PRB')
            ->whereBetween('bridging_sep.tglsep', [$tglAwal, $tglAkhir]);
    }
}
