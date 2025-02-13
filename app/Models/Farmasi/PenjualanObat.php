<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class PenjualanObat extends Model
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

    public function scopePenjualanObatMorfin(Builder $query, string $tglAwal = '', string $tglAkhir = '', string $bangsal = '', string $kodeObat = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            penjualan.nota_jual as no_rawat,
            penjualan.no_rkm_medis as no_rkm_medis,
            pasien.nm_pasien as nm_pasien,
            pasien.alamat as alamat,
            penjualan.tgl_jual as tgl_perawatan,
            detailjual.jumlah as jml,
            null as nm_dokter,
            "RS Samarinda Medika Citra" as alamat_dokter
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('pasien', 'penjualan.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('detailjual', 'penjualan.nota_jual', '=', 'detailjual.nota_jual')
            ->where('penjualan.kd_bangsal', $bangsal)
            ->where('detailjual.kode_brng', $kodeObat)
            ->whereBetween('penjualan.tgl_jual', [$tglAwal, $tglAkhir]);
    }

    public function scopeFilterFakturPajak(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->toDateString();
        }

        $tahun = substr($tglAwal, 0, 7);

        $sqlSelect = <<<'SQL'
            penjualan.nota_jual as no_rawat,
            'Walk In' as status_lanjut,
            'A09' as kd_pj,
            penjualan.no_rkm_medis,
            date(tagihan_sadewa.tgl_bayar) as tgl_bayar,
            date_format(tagihan_sadewa.tgl_bayar, '%H:%i:%s') as jam_bayar,
            tagihan_sadewa.jumlah_tagihan as totalbiaya,
            0 as diskon
            SQL;

        $this->addSearchConditions([
            'pasien.no_ktp',
            'pasien.nm_pasien',
            'pasien.alamat',
            'pasien.email',
            'pasien.no_tlp',
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->join('pasien', 'penjualan.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('tagihan_sadewa', 'penjualan.nota_jual', '=', 'tagihan_sadewa.no_nota')
            ->whereBetween('penjualan.tgl_jual', [$tahun.'-01', $tglAkhir])
            ->whereBetween('tagihan_sadewa.tgl_bayar', [$tglAwal.' 00:00:00.000', $tglAkhir.' 23:59:59.999']);
    }

    public function scopeLaporanFakturPajak(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->toDateString();
        }

        $tahun = substr($tglAwal, 0, 4);

        $sqlSelect = <<<'SQL'
            penjualan.nota_jual as no_rawat,
            '040' as kode_transaksi,
            date(tagihan_sadewa.tgl_bayar) as tgl_bayar,
            date_format(tagihan_sadewa.tgl_bayar, '%H:%i:%s') as jam_bayar,
            'Walk In' as status_lanjut,
            'Normal' as jenis_faktur,
            '' as keterangan_tambahan,
            '' as dokumen_pendukung,
            '' as cap_fasilitas,
            '' as id_tku_penjual,
            'National ID' as jenis_id,
            'IDN' as negara,
            '' as id_tku,
            penjualan.no_rkm_medis,
            pasien.no_ktp as nik_pasien,
            pasien.nm_pasien as nama_pasien,
            concat_ws(', ', pasien.alamat, kelurahan.nm_kel, kecamatan.nm_kec, kabupaten.nm_kab, propinsi.nm_prop) as alamat_pasien,
            pasien.email as email_pasien,
            pasien.no_tlp as no_telp_pasien,
            'A09' as kode_asuransi,
            'UMUM / PERSONAL' as nama_asuransi,
            '' as alamat_asuransi,
            '' as telp_asuransi,
            '' as email_asuransi,
            '' as npwp_asuransi,
            '' as kode_perusahaan,
            '' as nama_perusahaan,
            '' as alamat_perusahaan,
            '' as telp_perusahaan,
            '' as email_perusahaan,
            '' as npwp_perusahaan
            SQL;

        $this->addSearchConditions([
            'pasien.no_ktp',
            'pasien.nm_pasien',
            'pasien.alamat',
            'pasien.email',
            'pasien.no_tlp',
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->join('pasien', 'penjualan.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('tagihan_sadewa', 'penjualan.nota_jual', '=', 'tagihan_sadewa.no_nota')
            ->leftJoin('kelurahan', 'pasien.kd_kel', '=', 'kelurahan.kd_kel')
            ->leftJoin('kecamatan', 'pasien.kd_kec', '=', 'kecamatan.kd_kec')
            ->leftJoin('kabupaten', 'pasien.kd_kab', '=', 'kabupaten.kd_kab')
            ->leftJoin('propinsi', 'pasien.kd_prop', '=', 'propinsi.kd_prop')
            ->whereBetween('tagihan_sadewa.tgl_bayar', [$tglAwal.' 00:00:00.000', $tglAkhir.' 23:59:59.999'])
            ->whereBetween('penjualan.tgl_jual', [$tahun.'-01-01', $tglAkhir]);
    }
}
