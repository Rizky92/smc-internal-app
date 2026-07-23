<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TindakanRanapPerawat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'rawat_inap_pr';

    public $incrementing = false;

    public $timestamps = false;

    protected $searchColumns = [
        'no_rawat',
        'kd_jenis_prw',
        'nip',
        'tgl_perawatan'
    ];

    public function scopeItemFakturPajak(Builder $query): Builder
    {
        $sqlSelect = <<<'SQL'
            rawat_inap_pr.no_rawat,
            '080' as kode_transaksi,
            'B' as jenis_barang_jasa,
            '250100' as kode_barang_jasa,
            jns_perawatan_inap.nm_perawatan as nama_barang_jasa,
            '' as nama_satuan_ukur,
            rawat_inap_pr.biaya_rawat as harga_satuan,
            count(*) as jumlah_barang_jasa,
            0 as diskon_persen,
            0 as diskon_nominal,
            (rawat_inap_pr.biaya_rawat * count(*)) as dpp,
            12 as ppn_persen,
            0 as ppn_nominal,
            rawat_inap_pr.kd_jenis_prw,
            'Tindakan Ranap Pr' as kategori,
            7 as urutan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('jns_perawatan_inap', 'rawat_inap_pr.kd_jenis_prw', '=', 'jns_perawatan_inap.kd_jenis_prw')
            ->whereExists(fn ($q) => $q->from('regist_faktur')->whereColumn('regist_faktur.no_rawat', 'rawat_inap_pr.no_rawat'))
            ->groupBy(['rawat_inap_pr.no_rawat', 'rawat_inap_pr.kd_jenis_prw', 'jns_perawatan_inap.nm_perawatan', 'rawat_inap_pr.biaya_rawat']);
    }

    public function scopePenggunaanAlkes(Builder $query, string $tglAwal = '', string $tglAkhir = '', string $nama, string $cari = ''): Builder
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
            ->whereColumn('kamar_inap.no_rawat', 'rawat_inap_pr.no_rawat')
            ->whereNotIn('kamar_inap.stts_pulang', ['Pindah Kamar']);

        $kamar = (clone $base)
            ->selectRaw("concat(kamar_inap.kd_kamar, ' ', bangsal.nm_bangsal)")
            ->orderByDesc('kamar_inap.tgl_masuk')
            ->orderByDesc('kamar_inap.jam_masuk')
            ->limit(1);

        $searchKamar = $base->search($cari, ['kamar.kd_bangsal', 'bangsal.nm_bangsal']);

        $sql = $kamar->toSql();

        $sqlSelect = <<<SQL
            rawat_inap_pr.no_rawat,
            reg_periksa.no_rkm_medis,
            pasien.nm_pasien,
            rawat_inap_pr.kd_jenis_prw,
            jns_perawatan_inap.nm_perawatan,
            rawat_inap_pr.nip as nakes,
            petugas.nama as nama_nakes,
            rawat_inap_pr.tgl_perawatan as tgl_periksa,
            rawat_inap_pr.jam_rawat as jam,
            reg_periksa.kd_pj,
            penjab.png_jawab,
            ifnull(($sql), poliklinik.nm_poli) as unit,
            rawat_inap_pr.biaya_rawat as biaya,
            reg_periksa.status_lanjut as status
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

        return $query
            ->selectRaw($sqlSelect, $kamar->getBindings())
            ->join('petugas', 'rawat_inap_pr.nip', 'petugas.nip')
            ->join('jns_perawatan_inap', 'rawat_inap_pr.kd_jenis_prw', 'jns_perawatan_inap.kd_jenis_prw')
            ->join('reg_periksa', 'rawat_inap_pr.no_rawat', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', 'pasien.no_rkm_medis')
            ->join('penjab', 'reg_periksa.kd_pj', 'penjab.kd_pj')
            ->join('poliklinik', 'reg_periksa.kd_poli', 'poliklinik.kd_poli')
            ->whereBetween('rawat_inap_pr.tgl_perawatan', [$tglAwal, $tglAkhir])
            ->where('jns_perawatan_inap.nm_perawatan', 'like', '%'.$nama.'%');
    }
}
