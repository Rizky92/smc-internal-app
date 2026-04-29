<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PaketOperasi extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'paket_operasi';

    protected $primaryKey = 'kode_paket';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'kode_paket',
        'nm_perawatan',
        'kategori',
        'operator1',
        'operator2',
        'operator3',
        'asisten_operator1',
        'asisten_operator2',
        'asisten_operator3',
        'instrumen',
        'dokter_anak',
        'perawaat_resusitas',
        'dokter_anestesi',
        'asisten_anestesi',
        'asisten_anestesi2',
        'bidan',
        'bidan2',
        'bidan3',
        'perawat_luar',
        'sewa_ok',
        'alat',
        'akomodasi',
        'bagian_rs',
        'omloop',
        'omloop2',
        'omloop3',
        'omloop4',
        'omloop5',
        'sarpras',
        'dokter_pjanak',
        'dokter_umum',
        'kd_pj',
        'status',
        'kelas',
    ];

    public function scopeTarifOperasi(Builder $query): Builder
    {
        $this->addSearchConditions([
            'paket_operasi.kode_paket',
            'paket_operasi.nm_perawatan',
            'penjab.png_jawab',
        ]);

        $sqlSelect = <<<'SQL'
            paket_operasi.kode_paket,
            paket_operasi.nm_perawatan,
            paket_operasi.kategori,
            paket_operasi.operator1,
            paket_operasi.operator2,
            paket_operasi.operator3,
            paket_operasi.asisten_operator1,
            paket_operasi.asisten_operator2,
            paket_operasi.asisten_operator3,
            paket_operasi.instrumen,
            paket_operasi.dokter_anestesi,
            paket_operasi.asisten_anestesi,
            paket_operasi.asisten_anestesi2,
            paket_operasi.dokter_anak,
            paket_operasi.perawaat_resusitas,
            paket_operasi.bidan, 
            paket_operasi.bidan2,
            paket_operasi.bidan3,
            paket_operasi.perawat_luar,
            paket_operasi.alat,
            paket_operasi.sewa_ok,
            paket_operasi.akomodasi,
            paket_operasi.bagian_rs,
            paket_operasi.omloop,
            paket_operasi.omloop2,
            paket_operasi.omloop3,
            paket_operasi.omloop4,
            paket_operasi.omloop5,
            paket_operasi.sarpras,
            paket_operasi.dokter_pjanak,
            paket_operasi.dokter_umum, 
            (paket_operasi.operator1+paket_operasi.operator2+paket_operasi.operator3+paket_operasi.asisten_operator1+paket_operasi.asisten_operator2+paket_operasi.asisten_operator3+paket_operasi.instrumen+paket_operasi.dokter_anak+paket_operasi.perawaat_resusitas+paket_operasi.alat+paket_operasi.dokter_anestesi+paket_operasi.asisten_anestesi+paket_operasi.asisten_anestesi2+paket_operasi.bidan+paket_operasi.bidan2+paket_operasi.bidan3+paket_operasi.perawat_luar+paket_operasi.sewa_ok+paket_operasi.akomodasi+paket_operasi.bagian_rs+paket_operasi.omloop+paket_operasi.omloop2+paket_operasi.omloop3+paket_operasi.omloop4+paket_operasi.omloop5+paket_operasi.sarpras+paket_operasi.dokter_pjanak+paket_operasi.dokter_umum) jumlah, 
            penjab.png_jawab,
            paket_operasi.kelas
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('penjab', 'penjab.kd_pj', '=', 'paket_operasi.kd_pj')
            ->where('paket_operasi.status', '1');
    }
}
