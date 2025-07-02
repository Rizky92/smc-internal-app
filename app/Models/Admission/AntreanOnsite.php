<?php

namespace App\Models\Admission;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Reedware\LaravelCompositeRelations\HasCompositeRelations;

class AntreanOnsite extends Model
{
    use HasCompositeRelations;

    protected $connection = 'mysql_sik';

    protected $table = 'antriloketcetak_smc';

    protected $primaryKey = ['nomor', 'tanggal'];

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * @psalm-return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function scopePerminataanNomorAntreanOnsite(Builder $query, string $tglAwal, string $tglAkhir): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            antriloketcetak_smc.nomor,
            antriloketcetak_smc.tanggal,
            antriloketcetak_smc.jam,
            antriloketcetak_smc.jam_panggil,
            antriloketcetak_smc.no_rawat,
            antriloketcetak_smc.no_rkm_medis
        SQL;

        return $query->selectRaw($sqlSelect)
            ->whereBetween('tanggal', [$tglAwal, $tglAkhir]);
    }
}
