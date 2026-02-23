<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;
use App\Models\Kepegawaian\Pegawai;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Perawatan\RegistrasiPasien;
use App\Models\RekamMedis\Pasien;
use App\Models\RekamMedis\Penjamin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class PiutangDilunaskan extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'piutang_dilunaskan';

    protected $fillable = [
        'no_jurnal',
        'waktu_jurnal',
        'no_rawat',
        'no_rkm_medis',
        'no_tagihan',
        'kd_pj',
        'piutang_dibayar',
        'tgl_penagihan',
        'tgl_jatuh_tempo',
        'tgl_bayar',
        'status',
        'kd_rek',
        'nm_rek',
        'nik_penagih',
        'nik_menyetujui',
        'nik_validasi',
    ];

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'no_rkm_medis', 'no_rkm_medis');
    }

    public function penagih(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nik_penagih', 'nik');
    }

    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nik_menyetujui', 'nik');
    }

    public function pemvalidasi(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nik_validasi', 'nik');
    }

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class, 'no_jurnal', 'no_jurnal');
    }

    public function penjamin(): BelongsTo
    {
        return $this->belongsTo(Penjamin::class, 'kd_pj', 'kd_pj');
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(PenagihanPiutang::class, 'no_tagihan', 'no_tagihan');
    }

    public function registrasi(): BelongsTo
    {
        return $this->belongsTo(RegistrasiPasien::class, 'no_rawat', 'no_rawat');
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class, 'kd_rek', 'kd_rek');
    }

    public static function refreshModel(): void
    {
        $latest = static::query()->latest('waktu_jurnal')->value('waktu_jurnal') ?? '2022-10-30 23:59:59.999';

        Jurnal::query()
            ->jurnalPiutangDilunaskan($latest)
            ->cursor()
            ->each(function (Jurnal $jurnal) {
                $ket = str($jurnal->keterangan);

                $status = $ket->startsWith('BAYAR');
                $verifikator = $ket->afterLast('OLEH ')->trim()->value();

                $mapped = [
                    'no_jurnal'       => $jurnal->no_jurnal,
                    'waktu_jurnal'    => $jurnal->waktu_jurnal,
                    'no_rawat'        => $jurnal->no_rawat,
                    'no_rkm_medis'    => $jurnal->no_rkm_medis,
                    'no_tagihan'      => $jurnal->no_tagihan,
                    'kd_pj'           => $jurnal->kd_pj,
                    'piutang_dibayar' => $jurnal->besar_cicilan,
                    'tgl_penagihan'   => $jurnal->tgl_tagihan,
                    'tgl_jatuh_tempo' => $jurnal->tgl_jatuhtempo,
                    'tgl_bayar'       => $jurnal->tgl_bayar,
                    'status'          => $status ? 'Bayar' : 'Batal Bayar',
                    'kd_rek'          => $jurnal->kd_rek,
                    'nm_rek'          => $jurnal->nm_rek,
                    'nik_penagih'     => $jurnal->nip,
                    'nik_menyetujui'  => $jurnal->nip_menyetujui,
                    'nik_validasi'    => $verifikator,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];

                static::insert($mapped);
            });
    }

    public function scopeDataPiutangDilunaskan(
        Builder $query,
        string $tglAwal = '',
        string $tglAkhir = '',
        string $rekening = '-',
        string $berdasarkanTgl = 'jurnal'
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $filterTgl = [
            'jurnal'    => DB::raw('date(waktu_jurnal)'),
            'penagihan' => 'tgl_penagihan',
            'bayar'     => 'tgl_bayar',
        ][$berdasarkanTgl];

        $db = DB::connection('mysql_sik')->getDatabaseName();

        $sqlSelect = <<<'SQL'
            piutang_dilunaskan.*,
            jurnal.keterangan,
            concat(registrasi.umurdaftar, ' ', registrasi.sttsumur) umur,
            pasien.nm_pasien,
            if(penjamin.nama_perusahaan = '' or penjamin.nama_perusahaan = '-', penjamin.png_jawab, penjamin.nama_perusahaan) nama_penjamin,
            penagih.nama nama_penagih,
            penyetuju.nama nama_penyetuju,
            pemvalidasi.nama nama_pemvalidasi
            SQL;

        $this->addSearchConditions([
            'piutang_dilunaskan.no_jurnal',
            'jurnal.keterangan',
            'piutang_dilunaskan.no_rawat',
            'piutang_dilunaskan.no_tagihan',
            'piutang_dilunaskan.no_rkm_medis',
            'pasien.nm_pasien',
            'piutang_dilunaskan.kd_pj',
            "if(penjamin.nama_perusahaan = '' or penjamin.nama_perusahaan = '-', penjamin.png_jawab, penjamin.nama_perusahaan)",
            'piutang_dilunaskan.nik_penagih',
            "ifnull(penagih.nama, '-')",
            'piutang_dilunaskan.nik_menyetujui',
            "ifnull(penyetuju.nama, '-')",
            'piutang_dilunaskan.nik_validasi',
            "ifnull(pemvalidasi.nama, '-')",
            'piutang_dilunaskan.kd_rek',
            'piutang_dilunaskan.nm_rek',
        ]);

        $this->addRawColumns('nama_penjamin', DB::raw("if(penjamin.nama_perusahaan = '' or penjamin.nama_perusahaan = '-', penjamin.png_jawab, penjamin.nama_perusahaan)"));

        $jurnal = DB::raw("{$db}.jurnal jurnal");
        $registrasi = DB::raw("{$db}.reg_periksa registrasi");
        $pasien = DB::raw("{$db}.pasien pasien");
        $penjamin = DB::raw("{$db}.penjab penjamin");
        $penagih = DB::raw("{$db}.pegawai penagih");
        $penyetuju = DB::raw("{$db}.pegawai penyetuju");
        $pemvalidasi = DB::raw("{$db}.pegawai pemvalidasi");

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin($jurnal, 'piutang_dilunaskan.no_jurnal', '=', 'jurnal.no_jurnal')
            ->leftJoin($registrasi, 'piutang_dilunaskan.no_rawat', '=', 'registrasi.no_rawat')
            ->leftJoin($pasien, 'piutang_dilunaskan.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin($penjamin, 'piutang_dilunaskan.kd_pj', '=', 'penjamin.kd_pj')
            ->leftJoin($penagih, 'piutang_dilunaskan.nik_penagih', '=', 'penagih.nik')
            ->leftJoin($penyetuju, 'piutang_dilunaskan.nik_menyetujui', '=', 'penyetuju.nik')
            ->leftJoin($pemvalidasi, 'piutang_dilunaskan.nik_validasi', '=', 'pemvalidasi.nik')
            ->when($rekening !== '-', fn (Builder $q): Builder => $q->where('kd_rek', $rekening))
            ->whereBetween($filterTgl, [$tglAwal, $tglAkhir]);
    }
}
