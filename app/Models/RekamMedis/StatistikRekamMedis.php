<?php

namespace App\Models\RekamMedis;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StatistikRekamMedis extends Model
{
    protected $connection = 'mysql_smc';

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'rekam_medis';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeDenganPencarian(Builder $query, string $cari = ''): Builder
    {
        if (empty($cari)) {
            return $query;
        }

        return $query->where('no_rawat', 'LIKE', "%{$cari}%")
            ->orWhere('no_rkm_medis', 'LIKE', "%{$cari}%")
            ->orWhere('nm_pasien', 'LIKE', "%{$cari}%")
            ->orWhere('no_ktp', 'LIKE', "%{$cari}%")
            ->orWhere('agama', 'LIKE', "%{$cari}%")
            ->orWhere('nama_suku_bangsa', 'LIKE', "%{$cari}%")
            ->orWhere('status_lanjut', 'LIKE', "%{$cari}%")
            ->orWhere('status_poli', 'LIKE', "%{$cari}%")
            ->orWhere('status_perawatan', 'LIKE', "%{$cari}%")
            ->orWhere('diagnosa_awal', 'LIKE', "%{$cari}%")
            ->orWhere('kd_diagnosa', 'LIKE', "%{$cari}%")
            ->orWhere('nm_diagnosa', 'LIKE', "%{$cari}%")
            ->orWhere('kd_tindakan', 'LIKE', "%{$cari}%")
            ->orWhere('nm_tindakan', 'LIKE', "%{$cari}%")
            ->orWhere('nm_dokter', 'LIKE', "%{$cari}%")
            ->orWhere('dokter_poli', 'LIKE', "%{$cari}%")
            ->orWhere('rujuk_ke', 'LIKE', "%{$cari}%")
            ->orWhere('nm_poli', 'LIKE', "%{$cari}%")
            ->orWhere('png_jawab', 'LIKE', "%{$cari}%")
            ->orWhere('status_bayar', 'LIKE', "%{$cari}%")
            ->orWhere('stts_pulang', 'LIKE', "%{$cari}%")
            ->orWhere('no_tlp', 'LIKE', "%{$cari}%")
            ->orWhere('alamat', 'LIKE', "%{$cari}%");
    }
}
