<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;
use App\Models\Perawatan\RegistrasiPasien;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use RuntimeException;

class Billing extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'billing';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeTotalBillingan(Builder $query, $noRawat = null): Builder
    {
        if (empty($noRawat)) {
            throw new RuntimeException("Parameter of [\$noRawat] must not be empty.");
        }

        if (is_array($noRawat)) {
            $noRawat = collect($noRawat);
        }

        if ($noRawat instanceof Collection) {
            $noRawat = $noRawat
                ->map(fn (RegistrasiPasien $registrar): string => $registrar->no_rawat)
                ->values()
                ->all();
        }

        if ($noRawat instanceof RegistrasiPasien) {
            $noRawat = $noRawat->no_rawat;
        }

        if (is_string($noRawat)) {
            $noRawat = [$noRawat];
        }

        $kategoriBilling = [
            'Registrasi', 'Ralan Paramedis', 'Ralan Dokter', 'Ralan Dokter Paramedis',
            'Ranap Paramedis', 'Ranap Dokter', 'Ranap Dokter Paramedis', 'Obat', 'Resep Pulang',
            'Laborat', 'Radiologi', 'Potongan', 'Tambahan', 'Kamar', 'Service', 'Operasi',
            'Harian', 'Retur Obat'
        ];

        $sqlSelect = <<<SQL
            no_rawat,
            status,
            sum(totalbiaya) total
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->when(!is_null($noRawat), fn (Builder $query): Builder => $query->whereIn('no_rawat', $noRawat))
            ->whereIn('status', $kategoriBilling)
            ->groupBy(['no_rawat', 'status']);
    }
}
