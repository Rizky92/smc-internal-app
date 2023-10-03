<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Concerns\Searchable;
use App\Database\Eloquent\Concerns\Sortable;
use Illuminate\Database\Eloquent\Builder;
use App\Database\Eloquent\Model;

class ResepDokterRacikan extends Model
{
    use Searchable, Sortable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_resep';

    protected $keyType = 'string';

    protected $table = 'resep_dokter_racikan';

    public $incrementing = false;

    public $timestamps = false;

    protected array $searchColumns = [
        'no_resep',
        'no_racik',
        'nama_racik',
        'kd_racik',
        'aturan_pakai',
        'keterangan',
    ];

    public function scopeKunjunganResepObatRacikan(
        Builder $query,
        string $tglAwal = '',
        string $tglAkhir = '',
        string $jenisPerawatan = '',
        string $search
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
            resep_obat.tgl_perawatan,
            timestamp(resep_obat.tgl_perawatan, resep_obat.jam) as waktu_validasi,
            timestamp(resep_obat.tgl_penyerahan, resep_obat.jam_penyerahan) as waktu_penyerahan,
            resep_dokter_racikan.no_resep,
            pasien.nm_pasien,
            penjab.png_jawab,
            reg_periksa.status_lanjut,
            dokter.nm_dokter,
            poliklinik.nm_poli,
            round(sum(resep_dokter_racikan_detail.jml * databarang.h_beli)) total
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('resep_dokter_racikan_detail', 'resep_dokter_racikan.no_resep', '=', 'resep_dokter_racikan_detail.no_resep')
            ->join('resep_obat', 'resep_dokter_racikan.no_resep', '=', 'resep_obat.no_resep')
            ->join('reg_periksa', 'resep_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->join('databarang', 'resep_dokter_racikan_detail.kode_brng', '=', 'databarang.kode_brng')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('dokter', 'resep_obat.kd_dokter', '=', 'dokter.kd_dokter')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->where('reg_periksa.status_bayar', 'Sudah Bayar')
            ->whereBetween('resep_obat.tgl_perawatan', [$tglAwal, $tglAkhir])
            ->when(!empty($jenisPerawatan), fn (Builder $query) => $query->where('reg_periksa.status_lanjut', $jenisPerawatan))
            ->search($search, [
                'pasien.nm_pasien',
                'penjab.png_jawab',
                'reg_periksa.status_lanjut',
                'dokter.nm_dokter',
                'poliklinik.nm_poli',
            ])
            ->groupBy([
                'resep_dokter_racikan.no_resep',
                'dokter.nm_dokter',
                'resep_obat.tgl_perawatan',
                'resep_obat.jam',
                'pasien.nm_pasien',
                'reg_periksa.status_lanjut',
            ])
            ->withCasts([
                'tgl_perawatan' => 'date',
                'waktu_validasi' => 'datetime',
                'waktu_penyerahan' => 'datetime',
                'total' => 'float',
                'selisih' => 'datetime',
            ]);
    }
}
