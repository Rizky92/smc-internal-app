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
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
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

        $penjualan = PenjualanObat::query()->penjualanObatMorfin($tglAwal, $tglAkhir, $bangsal, $kodeObat);

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['jml' => 'int'])
            ->leftJoin('reg_periksa', 'detail_pemberian_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('kd_bangsal', $bangsal)
            ->where('detail_pemberian_obat.kode_brng', $kodeObat)
            ->whereBetween('detail_pemberian_obat.tgl_perawatan', [$tglAwal, $tglAkhir])
            ->union($penjualan);
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

    public function scopeItemFakturPajak(Builder $query): Builder
    {
        $sqlSelect = <<<'SQL'
            detail_pemberian_obat.no_rawat,
            case
                when reg_periksa.status_lanjut = 'Ranap' then '080'
                when reg_periksa.status_lanjut = 'Ralan' and reg_periksa.kd_pj = 'BPJ' then '030'
                else '040'
            end as kode_transaksi,
            'A' as jenis_barang_jasa,
            '300000' as kode_barang_jasa,
            databarang.nama_brng as nama_barang_jasa,
            databarang.kode_sat as nama_satuan_ukur,
            detail_pemberian_obat.biaya_obat as harga_satuan,
            sum(detail_pemberian_obat.jml) as jumlah_barang_jasa,
            0 as diskon_persen,
            0 as diskon_nominal,
            sum(detail_pemberian_obat.total) as dpp,
            12 as ppn_persen,
            0 as ppn_nominal,
            detail_pemberian_obat.kode_brng as kd_jenis_prw,
            'Pemberian Obat' as kategori,
            14 as urutan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('databarang', 'detail_pemberian_obat.kode_brng', '=', 'databarang.kode_brng')
            ->join('reg_periksa', 'detail_pemberian_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->whereExists(fn ($q) => $q->from('regist_faktur')->whereColumn('regist_faktur.no_rawat', 'detail_pemberian_obat.no_rawat'))
            ->groupBy(['detail_pemberian_obat.no_rawat', 'detail_pemberian_obat.kode_brng', 'databarang.nama_brng', 'detail_pemberian_obat.biaya_obat']);
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
