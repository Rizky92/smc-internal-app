<?php

namespace App\Models\Laboratorium;

use App\Support\Eloquent\Concerns\Searchable;
use App\Support\Eloquent\Concerns\Sortable;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Eloquent\Model;
use Reedware\LaravelCompositeRelations\CompositeBelongsTo;
use Reedware\LaravelCompositeRelations\HasCompositeRelations;

class HasilPeriksaLab extends Model
{
    use Searchable, Sortable, HasCompositeRelations;

    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'periksa_lab';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function permintaanLabPK(): CompositeBelongsTo
    {
        return $this
            ->compositeBelongsTo(
                PermintaanLabPK::class,
                ['no_rawat', 'tgl_hasil', 'jam_hasil'],
                ['no_rawat', 'tgl_periksa', 'jam']
            )
            ->where('kategori', 'PK');
    }

    /**
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function permintaanLabPA(): CompositeBelongsTo
    {
        return $this
            ->compositeBelongsTo(
                PermintaanLabPA::class,
                ['no_rawat', 'tgl_hasil', 'jam_hasil'],
                ['no_rawat', 'tgl_periksa', 'jam']
            )
            ->where('kategori', 'PA');
    }

    /**
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function permintaanLabMB(): CompositeBelongsTo
    {
        return $this
            ->compositeBelongsTo(
                PermintaanLabMB::class,
                ['no_rawat', 'tgl_hasil', 'jam_hasil'],
                ['no_rawat', 'tgl_periksa', 'jam']
            )
            ->where('kategori', 'MB');
    }

    public function scopeLaporanTindakanLab(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
            periksa_lab.no_rawat,
            reg_periksa.no_rkm_medis,
            pasien.nm_pasien,
            penjab.png_jawab,
            petugas.nama nama_petugas,
            periksa_lab.tgl_periksa,
            periksa_lab.jam,
            periksa_lab.dokter_perujuk,
            jns_perawatan_lab.kd_jenis_prw,
            jns_perawatan_lab.nm_perawatan,
            periksa_lab.kategori,
            periksa_lab.biaya,
            reg_periksa.status_bayar,
            periksa_lab.`status`,
            periksa_lab.kd_dokter,
            dokter.nm_dokter
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('reg_periksa', 'periksa_lab.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('petugas', 'periksa_lab.nip', '=', 'petugas.nip')
            ->leftJoin('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->leftJoin('dokter', 'periksa_lab.kd_dokter', '=', 'dokter.kd_dokter')
            ->leftJoin('jns_perawatan_lab', 'periksa_lab.kd_jenis_prw', '=', 'jns_perawatan_lab.kd_jenis_prw')
            ->whereBetween('periksa_lab.tgl_periksa', [$tglAwal, $tglAkhir]);
    }
}
