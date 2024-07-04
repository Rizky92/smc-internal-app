<?php

namespace App\Models\Antrian;

use App\Database\Eloquent\Model;
use App\Models\Kepegawaian\Dokter;
use App\Models\Perawatan\Poliklinik;
use App\Models\Perawatan\RegistrasiPasien;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Jadwal extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = ['kd_dokter', 'hari_kerja', 'jam_mulai'];

    protected $keyType = 'string';

    protected $table = 'jadwal';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * @psalm-return BelongsTo<Dokter>
     */
    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

    /**
     * @psalm-return BelongsTo<Poliklinik>
     */
    public function poliklinik(): BelongsTo
    {
        return $this->belongsTo(Poliklinik::class, 'kd_poli', 'kd_poli');
    }

    public function scopeJadwalDokter(Builder $query, bool $semuaPoli = false): Builder
    {
        $currentDate = now()->format('Y-m-d');

        $currentDay = now()->format('l');

        $currentDayDatabase = [
            'Sunday' => 'MINGGU',
            'Monday' => 'SENIN',
            'Tuesday' => 'SELASA',
            'Wednesday' => 'RABU',
            'Thursday' => 'KAMIS',
            'Friday' => 'JUMAT',
            'Saturday' => 'SABTU',
        ][$currentDay];

        $sqlSelect = <<<SQL
            d.kd_dokter,
            d.nm_dokter,
            p.kd_poli,
            p.nm_poli,
            j.hari_kerja,
            j.jam_mulai,
            j.jam_selesai,
            j.kuota,
            case 
                when row_num.jadwal_ke = 1 then 1
            else 2
            end as jadwal_ke,
            case
                when row_num.jadwal_ke = 1 then least((select count(*) from sik.reg_periksa rp where rp.kd_dokter = j.kd_dokter and rp.kd_poli = j.kd_poli and rp.tgl_registrasi = '{$currentDate}'), j.kuota)
            else 
                greatest( (select count(*) from sik.reg_periksa rp where rp.kd_dokter = j.kd_dokter and rp.kd_poli = j.kd_poli and rp.tgl_registrasi = '{$currentDate}') - (select j1.kuota from sik.jadwal j1 join (select jadwal_min.kd_dokter, jadwal_min.kd_poli, jadwal_min.hari_kerja, jadwal_min.jam_mulai, row_number() over (partition by jadwal_min.kd_dokter, jadwal_min.kd_poli, jadwal_min.hari_kerja order by jadwal_min.jam_mulai) as jadwal_ke from sik.jadwal jadwal_min where jadwal_min.hari_kerja = '{$currentDayDatabase}' ) row_num1 on j1.kd_dokter = row_num1.kd_dokter and j1.kd_poli = row_num1.kd_poli and j1.hari_kerja = row_num1.hari_kerja and j1.jam_mulai = row_num1.jam_mulai where row_num1.jadwal_ke = 1 and j1.kd_dokter = j.kd_dokter and j1.kd_poli = j.kd_poli and j1.hari_kerja = j.hari_kerja), 0)
            end as total_registrasi
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->from('sik.jadwal as j')
            ->join('sik.dokter as d', 'j.kd_dokter', '=', 'd.kd_dokter')
            ->join('sik.poliklinik as p', 'j.kd_poli', '=', 'p.kd_poli')
            ->join(DB::raw("
                (select 
                    jadwal_min.kd_dokter, jadwal_min.kd_poli, jadwal_min.hari_kerja, jadwal_min.jam_mulai,
                    row_number() over (partition by jadwal_min.kd_dokter, jadwal_min.kd_poli, jadwal_min.hari_kerja order by jadwal_min.jam_mulai) as jadwal_ke
                from sik.jadwal jadwal_min
                where jadwal_min.hari_kerja = '{$currentDayDatabase}'
                ) row_num"), function ($join) {
                $join->on('j.kd_dokter', '=', 'row_num.kd_dokter')
                    ->on('j.kd_poli', '=', 'row_num.kd_poli')
                    ->on('j.hari_kerja', '=', 'row_num.hari_kerja')
                    ->on('j.jam_mulai', '=', 'row_num.jam_mulai');
            })
            ->where('j.hari_kerja', '=', $currentDayDatabase)
            ->orderBy('j.kd_dokter', 'asc')
            ->orderBy('j.kd_poli', 'asc')
            ->orderBy('row_num.jadwal_ke', 'asc');
    }
}
