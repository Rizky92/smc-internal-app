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

        return $query->where('no_rawat', 'like', "%{$cari}%")
            ->orWhere('no_rm', 'like', "%{$cari}%")
            ->orWhere('pasien', 'like', "%{$cari}%")
            ->orWhere('nik', 'like', "%{$cari}%")
            ->orWhere('agama', 'like', "%{$cari}%")
            ->orWhere('suku', 'like', "%{$cari}%")
            ->orWhere('status_rawat', 'like', "%{$cari}%")
            ->orWhere('status_poli', 'like', "%{$cari}%")
            ->orWhere('asal_poli', 'like', "%{$cari}%")
            ->orWhere('dokter_poli', 'like', "%{$cari}%")
            ->orWhere('status_ralan', 'like', "%{$cari}%")
            ->orWhere('diagnosa_awal', 'like', "%{$cari}%")
            ->orWhere('kd_diagnosa', 'like', "%{$cari}%")
            ->orWhere('nm_diagnosa', 'like', "%{$cari}%")
            ->orWhere('kd_tindakan_ralan', 'like', "%{$cari}%")
            ->orWhere('nm_tindakan_ralan', 'like', "%{$cari}%")
            ->orWhere('kd_tindakan_ranap', 'like', "%{$cari}%")
            ->orWhere('nm_tindakan_ranap', 'like', "%{$cari}%")
            ->orWhere('dokter_pj', 'like', "%{$cari}%")
            ->orWhere('kelas', 'like', "%{$cari}%")
            ->orWhere('jenis_bayar', 'like', "%{$cari}%")
            ->orWhere('status_bayar', 'like', "%{$cari}%")
            ->orWhere('status_pulang_ranap', 'like', "%{$cari}%")
            ->orWhere('rujuk_keluar_rs', 'like', "%{$cari}%")
            ->orWhere('alamat', 'like', "%{$cari}%")
            ->orWhere('no_hp', 'like', "%{$cari}%");
    }
}
