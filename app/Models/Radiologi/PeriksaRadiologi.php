<?php

namespace App\Models\Radiologi;

use App\Casts\CastAsciiChars;
use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Reedware\LaravelCompositeRelations\CompositeBelongsTo;
use Reedware\LaravelCompositeRelations\HasCompositeRelations;

class PeriksaRadiologi extends Model
{
    use HasCompositeRelations;

    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'periksa_radiologi';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'hasil_pemeriksaan' => CastAsciiChars::class,
    ];

    public function permintaan(): CompositeBelongsTo
    {
        return $this->compositeBelongsTo(PermintaanRadiologi::class, ['no_rawat', 'tgl_hasil', 'jam_hasil'], ['no_rawat', 'tgl_periksa', 'jam']);
    }

    public function scopeLaporanTindakanRadiologi(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            periksa_radiologi.no_rawat,
            reg_periksa.no_rkm_medis,
            pasien.nm_pasien,
            penjab.png_jawab,
            petugas.nama nama_petugas,
            periksa_radiologi.tgl_periksa,
            periksa_radiologi.jam,
            periksa_radiologi.dokter_perujuk,
            jns_perawatan_radiologi.kd_jenis_prw,
            jns_perawatan_radiologi.nm_perawatan,
            periksa_radiologi.biaya,
            reg_periksa.status_bayar,
            periksa_radiologi.status,
            periksa_radiologi.kd_dokter,
            dokter.nm_dokter,
            ifnull(LEFT(hasil_radiologi.hasil, 200), '-') hasil_pemeriksaan
            SQL;

        $this->addSearchConditions([
            'periksa_radiologi.no_rawat',
            'reg_periksa.no_rkm_medis',
            'pasien.nm_pasien',
            'penjab.png_jawab',
            'petugas.nama',
            'periksa_radiologi.dokter_perujuk',
            'jns_perawatan_radiologi.kd_jenis_prw',
            'jns_perawatan_radiologi.nm_perawatan',
            'reg_periksa.status_bayar',
            'periksa_radiologi.status',
            'periksa_radiologi.kd_dokter',
            'dokter.nm_dokter',
            'hasil_radiologi.hasil',
        ]);

        $this->addRawColumns([
            'no_rawat'          => 'periksa_radiologi.no_rawat',
            'no_rkm_medis'      => 'reg_periksa.no_rkm_medis',
            'nm_pasien'         => 'pasien.nm_pasien',
            'png_jawab'         => 'penjab.png_jawab',
            'nama_petugas'      => 'petugas.nama',
            'tgl_periksa'       => 'periksa_radiologi.tgl_periksa',
            'jam'               => 'periksa_radiologi.jam',
            'dokter_perujuk'    => 'periksa_radiologi.dokter_perujuk',
            'kd_jenis_prw'      => 'jns_perawatan_radiologi.kd_jenis_prw',
            'nm_perawatan'      => 'jns_perawatan_radiologi.nm_perawatan',
            'biaya'             => 'periksa_radiologi.biaya',
            'status_bayar'      => 'reg_periksa.status_bayar',
            'status'            => 'periksa_radiologi.status',
            'kd_dokter'         => 'periksa_radiologi.kd_dokter',
            'nm_dokter'         => 'dokter.nm_dokter',
            'hasil_pemeriksaan' => DB::raw('LEFT(hasil_radiologi.hasil, 200)'),
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['biaya' => 'float'])
            ->leftJoin('reg_periksa', 'periksa_radiologi.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('petugas', 'periksa_radiologi.nip', '=', 'petugas.nip')
            ->leftJoin('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->leftJoin('dokter', 'periksa_radiologi.kd_dokter', '=', 'dokter.kd_dokter')
            ->leftJoin('jns_perawatan_radiologi', 'periksa_radiologi.kd_jenis_prw', '=', 'jns_perawatan_radiologi.kd_jenis_prw')
            ->leftJoin('hasil_radiologi', fn (JoinClause $join) => $join
                ->on('periksa_radiologi.no_rawat', '=', 'hasil_radiologi.no_rawat')
                ->on('periksa_radiologi.tgl_periksa', '=', 'hasil_radiologi.tgl_periksa')
                ->on('periksa_radiologi.jam', '=', 'hasil_radiologi.jam'))
            ->whereBetween('periksa_radiologi.tgl_periksa', [$tglAwal, $tglAkhir])
            ->groupByRaw('concat(
                periksa_radiologi.no_rawat,
                periksa_radiologi.tgl_periksa,
                periksa_radiologi.jam
            )');
    }

    public function scopeItemFakturPajak(Builder $query): Builder
    {
        $sqlSelect = <<<'SQL'
            periksa_radiologi.no_rawat,
            '080' as kode_transaksi,
            'B' as jenis_barang_jasa,
            '250100' as kode_barang_jasa,
            jns_perawatan_radiologi.nm_perawatan as nama_barang_jasa,
            '' as nama_satuan_ukur,
            periksa_radiologi.biaya as harga_satuan,
            count(*) as jumlah_barang_jasa,
            0 as diskon_persen,
            0 as diskon_nominal,
            (periksa_radiologi.biaya * count(*)) as dpp,
            12 as ppn_persen,
            0 as ppn_nominal,
            periksa_radiologi.kd_jenis_prw,
            'Radiologi' as kategori,
            11 as urutan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('jns_perawatan_radiologi', 'periksa_radiologi.kd_jenis_prw', '=', 'jns_perawatan_radiologi.kd_jenis_prw')
            ->whereExists(fn ($q) => $q->from('regist_faktur')->whereColumn('regist_faktur.no_rawat', 'periksa_radiologi.no_rawat'))
            ->groupBy(['periksa_radiologi.no_rawat', 'periksa_radiologi.kd_jenis_prw', 'jns_perawatan_radiologi.nm_perawatan', 'periksa_radiologi.biaya']);
    }
}
