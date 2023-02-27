<?php

namespace App\Models\Keuangan;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    use Searchable, Sortable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'billing';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeTotalBillingan(Builder $query, $noRawat = null): Builder
    {
        if ($noRawat instanceof Collection) {
            $noRawat = $noRawat
                ->map(fn ($registrar) => $registrar->no_rawat)
                ->values()
                ->toArray();
        }

        if (is_string($noRawat)) {
            $noRawat = [$noRawat];
        }

        $kategoriBilling = ['Registrasi', 'Ralan Paramedis', 'Ralan Dokter', 'Ralan Dokter Paramedis', 'Ranap Paramedis', 'Ranap Dokter', 'Ranap Dokter Paramedis', 'Obat', 'Resep Pulang', 'Laborat', 'Radiologi', 'Potongan', 'Tambahan', 'Kamar', 'Service', 'Operasi', 'Harian', 'Retur Obat'];

        $sqlSelect = "
            no_rawat,
            status,
            sum(totalbiaya) total
        ";

        return $query
            ->selectRaw($sqlSelect)
            ->when(!is_null($noRawat), fn (Builder $query) => $query->whereIn('no_rawat', $noRawat))
            ->whereIn('status', $kategoriBilling)
            ->groupBy(['no_rawat', 'status']);
    }
}
