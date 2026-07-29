<?php

namespace App\Models\Radiologi;

use App\Casts\CastAsciiChars;
use App\Database\Eloquent\Model;
use App\Models\Perawatan\KamarInap;
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
            bridging_sep.no_sep,
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
            'bridging_sep.no_sep',
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
            'no_sep'            => 'bridging_sep.no_sep',
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
            ->leftJoin('bridging_sep', fn (JoinClause $join) => $join
                ->on('reg_periksa.no_rawat', '=', 'bridging_sep.no_rawat')
                ->on('reg_periksa.status_lanjut', '=', DB::raw('(if(bridging_sep.jnspelayanan = "1", "Ranap", "Ralan"))'))
            )
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

    public function scopePenggunaanAlkes(Builder $query, string $tglAwal = '', string $tglAkhir = '', string $nama, array $exclude = [], string $cari = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth();
        }

        $base = KamarInap::query()
            ->join('kamar', 'kamar_inap.kd_kamar', 'kamar.kd_kamar')
            ->join('bangsal', 'kamar.kd_bangsal', 'bangsal.kd_bangsal')
            ->whereColumn('kamar_inap.no_rawat', 'periksa_radiologi.no_rawat')
            ->whereNotIn('kamar_inap.stts_pulang', ['Pindah Kamar']);

        $kamar = (clone $base)
            ->selectRaw("concat(kamar_inap.kd_kamar, ' ', bangsal.nm_bangsal)")
            ->orderByDesc('kamar_inap.tgl_masuk')
            ->orderByDesc('kamar_inap.jam_masuk')
            ->limit(1);

        $searchKamar = $base->search($cari, ['kamar.kd_bangsal', 'bangsal.nm_bangsal']);

        $sql = $kamar->toSql();

        $sqlSelect = <<<SQL
            periksa_radiologi.no_rawat,
            reg_periksa.no_rkm_medis,
            pasien.nm_pasien,
            periksa_radiologi.kd_jenis_prw,
            jns_perawatan_radiologi.nm_perawatan,
            periksa_radiologi.kd_dokter as nakes,
            dokter.nm_dokter as nama_nakes,
            periksa_radiologi.tgl_periksa,
            periksa_radiologi.jam,
            reg_periksa.kd_pj,
            penjab.png_jawab,
            if(periksa_radiologi.status = 'Ranap', ifnull(($sql), poliklinik.nm_poli), poliklinik.nm_poli) as unit,
            periksa_radiologi.biaya,
            periksa_radiologi.status as status
            SQL;

        $this->addSearchConditions([
            'reg_periksa.no_rkm_medis',
            'pasien.nm_pasien',
            'jns_perawatan_inap.nm_perawatan',
            'dokter.nm_dokter',
            'reg_periksa.kd_pj',
            'poliklinik.nm_poli',
            [
                'query'    => $searchKamar->toSql(),
                'bindings' => $searchKamar->getBindings(),
            ],
        ]);

        foreach ($exclude as $exc) {
            $query->where('jns_perawatan_radiologi.nm_perawatan', 'not like', '%'.$exc.'%');
        }

        return $query
            ->selectRaw($sqlSelect, $kamar->getBindings())
            ->join('dokter', 'periksa_radiologi.kd_dokter', 'dokter.kd_dokter')
            ->join('jns_perawatan_radiologi', 'periksa_radiologi.kd_jenis_prw', 'jns_perawatan_radiologi.kd_jenis_prw')
            ->join('reg_periksa', 'periksa_radiologi.no_rawat', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', 'pasien.no_rkm_medis')
            ->join('penjab', 'reg_periksa.kd_pj', 'penjab.kd_pj')
            ->join('poliklinik', 'reg_periksa.kd_poli', 'poliklinik.kd_poli')
            ->whereBetween('periksa_radiologi.tgl_periksa', [$tglAwal, $tglAkhir])
            ->where(function (Builder $q) use ($nama) {
                $method = 'where';
                foreach ((array) $nama as $n) {
                    $q->{$method}('jns_perawatan_radiologi.nm_perawatan', 'like', '%'.$n.'%');
                    $method = 'orWhere';
                }
            });
    }
}
