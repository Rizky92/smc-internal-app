<?php

namespace App\Models\Antrian;

use App\Database\Eloquent\Model;
use App\Models\Kepegawaian\Dokter;
use App\Models\Perawatan\Poliklinik;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        $sqlSelect = <<<SQL
            dokter.kd_dokter,
            dokter.nm_dokter,
            poliklinik.kd_poli, 
            poliklinik.nm_poli,
            jadwal.hari_kerja,
            jadwal.jam_mulai, 
            jadwal.jam_selesai,
            jadwal.kuota
        SQL;
                        
        $this->addSearchConditions([
            'dokter.nm_dokter',
            'poliklinik.nm_poli',
        ]);

        $dayOfWeekMap = [
            'Sunday'    => 'AHAD',
            'Monday'    => 'SENIN',
            'Tuesday'   => 'SELASA',
            'Wednesday' => 'RABU',
            'Thursday'  => 'KAMIS',
            'Friday'    => 'JUMAT',
            'Saturday'  => 'SABTU',
        ];

        return $query
            ->selectRaw($sqlSelect)
            ->join('dokter', 'jadwal.kd_dokter', '=', 'dokter.kd_dokter')
            ->join('poliklinik', 'jadwal.kd_poli', '=', 'poliklinik.kd_poli')
            ->where('jadwal.hari_kerja', '=', strtoupper($dayOfWeekMap[date('l')]))
            ->orderByRaw("CASE WHEN poliklinik.nm_poli = 'Poli Eksekutif' THEN 1 ELSE 0 END, poliklinik.nm_poli");
    }

    public function scopeTotalRegistrasi (Builder $query) :Builder
    { 
        return $query
        ->select('kd_dokter','kd_poli', DB::raw('count(*) as total_registrasi'))
        ->where('tgl_registrasi', now()->format('Y-m-d'))
        ->groupBy('kd_dokter','kd_poli');

    }

    public function scopeTotalKuota (Builder $query) :Builder
    {
        $dayOfWeekMap = [
            'Sunday' => 'AHAD',
            'Monday' => 'SENIN',
            'Tuesday' => 'SELASA',
            'Wednesday' => 'RABU',
            'Thursday' => 'KAMIS',
            'Friday' => 'JUMAT',
            'Saturday' => 'SABTU',
        ];
        
        return $query
        ->select(['kd_dokter','kd_poli', DB::raw('sum(kuota) as total_kuota')])
        ->where('jadwal.hari_kerja', '=', strtoupper($dayOfWeekMap[date('l')]))
        ->groupBy('kd_dokter','kd_poli');
    }

    public static function hitungTotalRegistrasi($kd_dokter, $kd_poli, $hari_kerja, $tgl_registrasi)
    {
        // Mendapatkan dua jadwal dengan kondisi yang diinginkan
        $jadwal1 = self::where('kd_dokter', $kd_dokter)
            ->where('kd_poli', $kd_poli)
            ->where('hari_kerja', $hari_kerja)
            ->orderBy('jam_mulai', 'asc') // Urutkan berdasarkan jam_mulai
            ->first();

        $jadwal2 = self::where('kd_dokter', $kd_dokter)
            ->where('kd_poli', $kd_poli)
            ->where('hari_kerja', $hari_kerja)
            ->where('jam_mulai', '>', $jadwal1->jam_mulai) // Jadwal yang jam_mulai lebih lambat
            ->orderBy('jam_mulai', 'asc') // Urutkan berdasarkan jam_mulai
            ->first();

        // Jika ada dua jadwal dengan kondisi tersebut
        if ($jadwal1 && $jadwal2) {
            // Cek apakah total_registrasi pada jadwal yang jam_mulai lebih awal telah mencapai kuota
            $total_registrasi_jadwal1 = RegistrasiPasien::where('kd_dokter', $kd_dokter)
                ->where('kd_poli', $kd_poli)
                ->where('tgl_registrasi', $tgl_registrasi)
                ->count();

            // Hitung total registrasi seperti biasa untuk jadwal yang jam_mulai lebih awal
            $total_registrasi_jadwal1 = min($total_registrasi_jadwal1, $jadwal1->kuota);

            // Hitung total registrasi untuk jadwal yang jam_mulai lebih lambat
            $total_registrasi_jadwal2 = RegistrasiPasien::where('kd_dokter', $kd_dokter)
                ->where('kd_poli', $kd_poli)
                ->where('tgl_registrasi', $tgl_registrasi)
                ->count();

            // Kurangi total registrasi jadwal pertama dengan kuota jadwal pertama
            $total_registrasi_jadwal2 = max(0, $total_registrasi_jadwal2 - $total_registrasi_jadwal1);

            return [$total_registrasi_jadwal1, $total_registrasi_jadwal2];
        }

        // Jika hanya ada satu jadwal atau tidak ada jadwal yang memenuhi kondisi
        return [0, 0];
    }
}
