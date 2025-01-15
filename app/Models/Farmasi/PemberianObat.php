<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PemberianObat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = null;

    protected $table = 'detail_pemberian_obat';

    public $incrementing = false;

    public $timestamps = false;

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class, 'kode_brng', 'kode_brng');
    }

    public function scopeLaporanPemakaianObatMorphine(Builder $query, string $tglAwal, string $tglAkhir, string $bangsal, string $kodeObat): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = <<<'SQL'
            detail_pemberian_obat.no_rawat,
            pasien.no_rkm_medis,
            pasien.nm_pasien,
            pasien.alamat,
            detail_pemberian_obat.tgl_perawatan,
            detail_pemberian_obat.jml,
            dokter.nm_dokter,
            "RS Samarinda Medika Citra" alamat_dokter
        SQL;

        $this->addSearchConditions([
            'pasien.no_rkm_medis',
            'pasien.nm_pasien',
            'pasien.alamat',
            'dokter.kd_dokter',
            'dokter.nm_dokter',
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['jml' => 'int'])
            ->leftJoin('reg_periksa', 'detail_pemberian_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('kd_bangsal', $bangsal)
            ->where('detail_pemberian_obat.kode_brng', $kodeObat)
            ->whereBetween('detail_pemberian_obat.tgl_perawatan', [$tglAwal, $tglAkhir]);
    }

    public function scopePendapatanObat(Builder $query, string $year = '2022', string $jenisPerawatan = ''): Builder
    {
        $sqlSelect = <<<'SQL'
            round(sum(detail_pemberian_obat.total)) jumlah,
            month(detail_pemberian_obat.tgl_perawatan) bulan
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['jumlah' => 'float', 'bulan' => 'int'])
            ->leftJoin('reg_periksa', 'detail_pemberian_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('databarang', 'detail_pemberian_obat.kode_brng', '=', 'databarang.kode_brng')
            ->whereBetween('detail_pemberian_obat.tgl_perawatan', ["{$year}-01-01", "{$year}-12-31"])
            ->when(! empty($jenisPerawatan), function (Builder $query) use ($jenisPerawatan) {
                switch (Str::lower($jenisPerawatan)) {
                    case 'ralan':
                        return $query->where('detail_pemberian_obat.status', 'Ralan')
                            ->where('reg_periksa.kd_poli', '!=', 'IGDK');
                    case 'ranap':
                        return $query->where('detail_pemberian_obat.status', 'Ranap');
                    case 'igd':
                        return $query->where('detail_pemberian_obat.status', 'Ralan')
                            ->where('reg_periksa.kd_poli', '=', 'IGDK');
                    case 'alkes':
                        return $query->where('databarang.kode_kategori', 'like', '3.%');
                }
            })
            ->groupByRaw('month(detail_pemberian_obat.tgl_perawatan)')
            ->withCasts(['jumlah' => 'float', 'bulan' => 'int']);
    }

    public function scopeItemFakturPajak(Builder $query, array $noRawat = []): Builder
    {
        if ($noRawat === []) {
            return $query;
        }

        $sqlSelect = <<<SQL
            detail_pemberian_obat.no_rawat,
            detail_pemberian_obat.kode_brng as kd_jenis_prw,
            databarang.nama_brng as nm_perawatan,
            detail_pemberian_obat.biaya_obat as biaya_rawat,
            sum(detail_pemberian_obat.embalase) as embalase,
            sum(detail_pemberian_obat.tuslah) as tuslah,
            0 as diskon,
            0 as tambahan,
            sum(detail_pemberian_obat.jml) as jml,
            sum(detail_pemberian_obat.total) as subtotal,
            'Pemberian Obat' as kategori
            SQL;
        
        return $query
            ->selectRaw($sqlSelect)
            ->join('databarang', 'detail_pemberian_obat.kode_brng', '=', 'databarang.kode_brng')
            ->whereIn('detail_pemberian_obat.kode_brng', $noRawat);
            ->groupBy(['detail_pemberian_obat.no_rawat', 'detail_pemberian_obat.kode_brng', 'databarang.nama_brng', 'detail_pemberian_obat.h_beli']);
    }

    public static function pendapatanObatRalan(string $year = '2022'): array
    {
        $data = static::pendapatanObat($year, 'ralan')->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }

    public static function pendapatanObatRanap(string $year = '2022'): array
    {
        $data = static::pendapatanObat($year, 'ranap')->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }

    public static function pendapatanObatIGD(string $year = '2022'): array
    {
        $data = static::pendapatanObat($year, 'IGD')->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }

    public static function pendapatanAlkesUnit(string $year = '2022'): array
    {
        $data = static::pendapatanObat($year, 'alkes')->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }
}
