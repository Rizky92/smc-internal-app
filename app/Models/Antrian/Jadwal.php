<?php

namespace App\Models\Antrian;

use App\Database\Eloquent\Model;
use App\Models\Kepegawaian\Dokter;
use App\Models\Perawatan\Poliklinik;
use App\Models\Perawatan\RegistrasiPasien;
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
        $sqlSelect = <<<'SQL'
            dokter.kd_dokter,
            dokter.nm_dokter,
            poliklinik.kd_poli, 
            poliklinik.nm_poli,
            jadwal.hari_kerja,
            DATE_FORMAT(jadwal.jam_mulai, '%H:%i') AS jam_mulai, 
            DATE_FORMAT(jadwal.jam_selesai, '%H:%i') AS jam_selesai,
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
            ->orderByRaw("CASE WHEN poliklinik.nm_poli = 'Poli Eksekutif' THEN 1 ELSE 0 END, poliklinik.nm_poli")
            ->orderBy('poliklinik.nm_poli', 'asc')
            ->orderBy('dokter.nm_dokter', 'asc')
            ->orderBy('jadwal.jam_mulai', 'asc');
    }

    public function isDuplicate()
    {
        return Jadwal::where('kd_dokter', $this->kd_dokter)
            ->where('kd_poli', $this->kd_poli)
            ->where('hari_kerja', $this->hari_kerja)
            ->count() > 1;
    }

    public static function hitungTotalRegistrasi($kd_dokter, $kd_poli, $hari_kerja, $tgl_registrasi)
    {
        // Mencari jadwal dengan kondisi yang diinginkan
        $jadwal = self::where('kd_dokter', $kd_dokter)
            ->where('kd_poli', $kd_poli)
            ->where('hari_kerja', $hari_kerja)
            ->orderBy('jam_mulai', 'asc')
            ->get();

        // Jika terdapat lebih dari satu jadwal dengan kondisi tersebut
        if ($jadwal->count() > 1) {
            // Ambil jadwal pertama dan kedua
            $jadwal1 = $jadwal->first();
            $jadwal2 = $jadwal->last();

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
        } elseif ($jadwal->count() == 1) {
            // Jika hanya ada satu jadwal dengan kondisi tersebut
            $jadwal1 = $jadwal->first();

            // Hitung total registrasi seperti biasa untuk jadwal tersebut
            $total_registrasi_jadwal1 = RegistrasiPasien::where('kd_dokter', $kd_dokter)
                ->where('kd_poli', $kd_poli)
                ->where('tgl_registrasi', $tgl_registrasi)
                ->count();

            return [$total_registrasi_jadwal1, 0];
        } else {
            // Jika tidak ada jadwal yang memenuhi kondisi
            // Hitung total registrasi untuk semua jadwal tanpa batasan kuota
            $total_registrasi = RegistrasiPasien::where('kd_dokter', $kd_dokter)
                ->where('kd_poli', $kd_poli)
                ->where('tgl_registrasi', $tgl_registrasi)
                ->sum('total_registrasi');

            return [$total_registrasi, 0];
        }
    }
}
