<?php

namespace App\Models\Farmasi;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PemberianObat extends Model
{
    use Searchable, Sortable;

    protected $connection = 'mysql_sik';
    
    protected $primaryKey = false;

    protected $keyType = null;

    protected $table = 'detail_pemberian_obat';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeLaporanPemakaianObatMorphine(Builder $query, string $tglAwal = '', string $tglAkhir = '', string $kodeObat): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
            detail_pemberian_obat.no_rawat,
            pasien.no_rkm_medis,
            pasien.nm_pasien,
            pasien.alamat,
            detail_pemberian_obat.tgl_perawatan,
            detail_pemberian_obat.jml,
            dokter.nm_dokter,
            "RS Samarinda Medika Citra" alamat_dokter
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('reg_periksa', 'detail_pemberian_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('detail_pemberian_obat.kode_brng', $kodeObat)
            ->whereBetween('detail_pemberian_obat.tgl_perawatan', [$tglAwal, $tglAkhir])
            ->withCasts(['jml' => 'int']);
    }

    public function scopePendapatanObat(Builder $query, string $jenisPerawatan = '', string $year = '2022', bool $selainFarmasi = false): Builder
    {
        $date = carbon()->setYear(intval($year))->startOfYear()->toPeriod(carbon()->setYear(intval($year))->endOfYear());
        
        return $query->selectRaw("
            round(sum(detail_pemberian_obat.total)) jumlah,
            month(detail_pemberian_obat.tgl_perawatan) bulan
        ")
            ->leftJoin('reg_periksa', 'detail_pemberian_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('databarang', 'detail_pemberian_obat.kode_brng', '=', 'databarang.kode_brng')
            ->whereBetween('detail_pemberian_obat.tgl_perawatan', [$date->startDate, $date->endDate])
            ->when(!empty($jenisPerawatan), function (Builder $query) use ($jenisPerawatan) {
                switch (Str::lower($jenisPerawatan)) {
                    case 'ralan':
                        return $query->where('detail_pemberian_obat.status', 'Ralan')
                            ->where('reg_periksa.kd_poli', '!=', 'IGDK');
                    case 'ranap':
                        return $query->where('detail_pemberian_obat.status', 'Ranap');
                    case 'igd':
                        return $query->where('detail_pemberian_obat.status', 'Ralan')
                            ->where('reg_periksa.kd_poli', '=', 'IGDK');
                }
            })
            ->when($selainFarmasi, fn (Builder $q): Builder => $q->where('databarang.kode_kategori', 'like', '3.%'))
            ->groupByRaw('month(detail_pemberian_obat.tgl_perawatan)')
            ->withCasts(['jumlah' => 'float', 'bulan' => 'int']);
    }

    public static function pendapatanObatRalan(string $year = '2022'): array
    {
        $data = static::pendapatanObat('ralan', $year)->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }

    public static function pendapatanObatRanap(string $year = '2022'): array
    {
        $data = static::pendapatanObat('ranap', $year)->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }

    public static function pendapatanObatIGD(string $year = '2022'): array
    {
        $data = static::pendapatanObat('IGD', $year)->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }

    public static function pendapatanAlkesUnit(string $year = '2022'): array
    {
        $data = static::pendapatanObat('', $year, true)->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }
}
