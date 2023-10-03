<?php

namespace App\Models\Keuangan;

use App\Models\Kepegawaian\Pegawai;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Perawatan\RegistrasiPasien;
use App\Models\RekamMedis\Pasien;
use App\Models\RekamMedis\Penjamin;
use App\Database\Eloquent\Concerns\Searchable;
use App\Database\Eloquent\Concerns\Sortable;
use Illuminate\Database\Eloquent\Builder;
use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PiutangDilunaskan extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_smc';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $table = 'piutang_dilunaskan';

    public $incrementing = true;

    public $timestamps = true;

    public static function refreshModel(): void
    {
        $latest = static::query()->latest('waktu_jurnal')->value('waktu_jurnal');

        $sqlSelect = <<<SQL
            jurnal.no_jurnal,
            timestamp(jurnal.tgl_jurnal, jurnal.jam_jurnal) waktu_jurnal,
            detail_penagihan_piutang.no_rawat,
            bayar_piutang.no_rkm_medis,
            penagihan_piutang.no_tagihan,
            penagihan_piutang.kd_pj,
            bayar_piutang.besar_cicilan,
            penagihan_piutang.tanggal tgl_penagihan,
            penagihan_piutang.tanggaltempo tgl_jatuh_tempo,
            bayar_piutang.tgl_bayar,
            penagihan_piutang.kd_rek,
            akun_penagihan_piutang.nama_bank,
            penagihan_piutang.nip,
            penagihan_piutang.nip_menyetujui,
            jurnal.keterangan
        SQL;

        DB::connection('mysql_sik')
            ->table('jurnal')
            ->selectRaw($sqlSelect)
            ->leftJoin('detail_penagihan_piutang', 'jurnal.no_bukti', '=', 'detail_penagihan_piutang.no_rawat')
            ->leftJoin('penagihan_piutang', 'detail_penagihan_piutang.no_tagihan', '=', 'penagihan_piutang.no_tagihan')
            ->join(DB::raw('pegawai pegawai_penagih'), 'penagihan_piutang.nip', '=', 'pegawai_penagih.nik')
            ->join(DB::raw('pegawai pegawai_menyetujui'), 'penagihan_piutang.nip_menyetujui', '=', 'pegawai_menyetujui.nik')
            ->join('penjab', 'penagihan_piutang.kd_pj', '=', 'penjab.kd_pj')
            ->join('akun_penagihan_piutang', 'penagihan_piutang.kd_rek', '=', 'akun_penagihan_piutang.kd_rek')
            ->join('bayar_piutang', fn (JoinClause $join) => $join
                ->on('detail_penagihan_piutang.no_rawat', '=', 'bayar_piutang.no_rawat')
                ->on('penagihan_piutang.kd_rek', '=', 'bayar_piutang.kd_rek'))
            ->when(
                !is_null($latest),
                fn (DatabaseBuilder $q): DatabaseBuilder => $q->whereRaw("timestamp(tgl_jurnal, jam_jurnal) > ?", $latest),
                fn (DatabaseBuilder $q): DatabaseBuilder => $q->where('tgl_jurnal', '>=', '2022-10-31')
            )
            ->where('penagihan_piutang.status', 'Sudah Dibayar')
            ->where(fn (DatabaseBuilder $q): DatabaseBuilder => $q
                ->where('keterangan', 'like', '%bayar piutang, oleh%')
                ->orWhere('keterangan', 'like', '%pembatalan bayar piutang, oleh%'))
            ->orderBy('jurnal.tgl_jurnal')
            ->orderBy('jurnal.jam_jurnal')
            ->cursor()
            ->each(function (object $jurnal): void {
                $ket = Str::of($jurnal->keterangan);

                $status = $ket->startsWith('BAYAR');
                $verifikator = $ket->afterLast('OLEH ')->trim();

                $mapped = [
                    'no_jurnal'       => $jurnal->no_jurnal,
                    'waktu_jurnal'    => $jurnal->waktu_jurnal,
                    'no_rawat'        => $jurnal->no_rawat,
                    'no_rkm_medis'    => $jurnal->no_rkm_medis,
                    'no_tagihan'      => $jurnal->no_tagihan,
                    'kd_pj'           => $jurnal->kd_pj,
                    'piutang_dibayar' => $jurnal->besar_cicilan,
                    'tgl_penagihan'   => $jurnal->tgl_penagihan,
                    'tgl_jatuh_tempo' => $jurnal->tgl_jatuh_tempo,
                    'tgl_bayar'       => $jurnal->tgl_bayar,
                    'status'          => $status ? 'Bayar' : 'Batal Bayar',
                    'kd_rek'          => $jurnal->kd_rek,
                    'nm_rek'          => $jurnal->nama_bank,
                    'nik_penagih'     => $jurnal->nip,
                    'nik_menyetujui'  => $jurnal->nip_menyetujui,
                    'nik_validasi'    => (string) $verifikator,
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
        string $rekening = '112010',
        string $berdasarkanTgl = 'jurnal'
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $filterTgl = [
            'jurnal'    => DB::raw("date(waktu_jurnal)"),
            'penagihan' => 'tgl_penagihan',
            'bayar'     => 'tgl_bayar',
        ][$berdasarkanTgl];

        $db = DB::connection('mysql_sik')->getDatabaseName();

        $sqlSelect = <<<SQL
            piutang_dilunaskan.*,
            jurnal.keterangan,
            concat(registrasi.umurdaftar, ' ', registrasi.sttsumur) umur,
            pasien.nm_pasien,
            if(penjamin.nama_perusahaan = '' or penjamin.nama_perusahaan = '-', penjamin.png_jawab, penjamin.nama_perusahaan) nama_penjamin,
            penagih.nama nama_penagih,
            penyetuju.nama nama_penyetuju,
            pemvalidasi.nama nama_pemvalidasi
        SQL;

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
            ->where('kd_rek', $rekening)
            ->whereBetween($filterTgl, [$tglAwal, $tglAkhir]);
    }

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
}
