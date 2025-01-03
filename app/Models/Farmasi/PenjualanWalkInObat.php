<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class PenjualanWalkInObat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'nota_jual';

    protected $keyType = 'string';

    protected $table = 'penjualan';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeKunjunganWalkIn(Builder $query, string $year = '2022'): Builder
    {
        $sqlSelect = <<<'SQL'
            count(penjualan.nota_jual) jumlah,
            month(penjualan.tgl_jual) bulan
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['jumlah' => 'int', 'bulan' => 'int'])
            ->where('status', 'Sudah Dibayar')
            ->whereBetween('tgl_jual', ["{$year}-01-01", "{$year}-12-31"])
            ->groupByRaw('month(penjualan.tgl_jual)');
    }

    public function scopePendapatanWalkIn(Builder $query, string $year = '2022'): Builder
    {
        $sqlSelect = <<<'SQL'
            round(sum(detail_jual.total + penjualan.ppn)) jumlah,
            month(penjualan.tgl_jual) bulan
        SQL;

        $sumDetailJual = DB::connection('mysql_sik')
            ->table('detailjual')
            ->select([DB::raw('sum(detailjual.subtotal) total'), 'detailjual.nota_jual'])
            ->groupBy('detailjual.nota_jual');

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['jumlah' => 'float', 'bulan' => 'int'])
            ->leftJoinSub($sumDetailJual, 'detail_jual', fn (JoinClause $join) => $join->on('penjualan.nota_jual', '=', 'detail_jual.nota_jual'))
            ->where('penjualan.status', 'Sudah Dibayar')
            ->whereBetween('penjualan.tgl_jual', ["{$year}-01-01", "{$year}-12-31"])
            ->groupByRaw('month(penjualan.tgl_jual)');
    }

    public function detail(): BelongsToMany
    {
        return $this->belongsToMany(Obat::class, 'detailjual', 'nota_jual', 'kode_brng');
    }

    public static function totalKunjunganWalkIn(string $year = '2022'): array
    {
        $data = static::kunjunganWalkIn($year)->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }

    public static function totalPendapatanWalkIn(string $year = '2022'): array
    {
        $data = static::pendapatanWalkIn($year)->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }

    public function scopeItemPenjualanWalkIn(Builder $query,string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->format('Y-m-d');
        }

        $sqlSelect = <<<'SQL'
            penjualan.nota_jual,
            penjualan.tgl_jual,
            pasien.no_ktp,
            pasien.nm_pasien,
            concat_ws(', ', pasien.alamat, kelurahan.nm_kel, kecamatan.nm_kec, kabupaten.nm_kab, propinsi.nm_prop) as alamat,
            pasien.no_tlp,
            penjualan.status,
            penjualan.jns_jual,
            databarang.nama_brng,
            detailjual.h_jual,
            detailjual.jumlah,
            detailjual.total
            SQL;

        $this->addSearchConditions([
            'penjualan.nota_jual',
            'pasien.no_ktp',
            'pasien.nm_pasien',
            'concat_ws(\', \', pasien.alamat, kelurahan.nm_kel, kecamatan.nm_kec, kabupaten.nm_kab, propinsi.nm_prop)',
            'pasien.no_tlp',
            'penjualan.status',
            'penjualan.jns_jual',
            'databarang.nama_brng',
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->join('detailjual', 'penjualan.nota_jual', '=', 'detailjual.nota_jual')
            ->join('databarang', 'detailjual.kode_brng', '=', 'databarang.kode_brng')
            ->join('pasien', 'penjualan.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('kelurahan', 'pasien.kd_kel', '=', 'kelurahan.kd_kel')
            ->join('kecamatan', 'pasien.kd_kec', '=', 'kecamatan.kd_kec')
            ->join('kabupaten', 'pasien.kd_kab', '=', 'kabupaten.kd_kab')
            ->join('propinsi', 'pasien.kd_prop', '=', 'propinsi.kd_prop')
            ->whereBetween('penjualan.tgl_jual', [$tglAwal, $tglAkhir])
            ->whereRaw('detailjual.total != 0');
    }
}
