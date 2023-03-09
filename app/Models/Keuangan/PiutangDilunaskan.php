<?php

namespace App\Models\Keuangan;

use App\Models\Kepegawaian\Pegawai;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Perawatan\RegistrasiPasien;
use App\Models\RekamMedis\Penjamin;
use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PiutangDilunaskan extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_smc';

    protected $table = 'piutang_dilunaskan';

    public static function refreshModel()
    {
        $latest = static::latest('waktu_jurnal')->value('waktu_jurnal');

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
            ->leftJoin('bayar_piutang', fn (JoinClause $join) => $join
                ->on('detail_penagihan_piutang.no_rawat', '=', 'bayar_piutang.no_rawat')
                ->on('akun_penagihan_piutang.kd_rek', '=', 'bayar_piutang.kd_rek'))
            ->when(
                !is_null($latest),
                fn ($q) => $q->whereRaw("timestamp(tgl_jurnal, jam_jurnal) > ?", $latest->waktu_jurnal),
                fn ($q) => $q->where('tgl_jurnal', '>=', '2022-10-31')
            )
            ->where('penagihan_piutang.status', 'Sudah Dibayar')
            ->where('akun_penagihan_piutang.kd_rek', '112010')
            ->where(fn ($q) => $q
                ->where('keterangan', 'like', '%bayar piutang, oleh%')
                ->orWhere('keterangan', 'like', '%pembatalan bayar piutang, oleh%'))
            ->orderBy('jurnal.tgl_jurnal')
            ->orderBy('jurnal.jam_jurnal')
            ->chunk(500, function (Collection $collection) {
                $data = $collection->map(function ($jurnal, $key) {
                    $ket = Str::of($jurnal->keterangan);

                    $status = $ket->startsWith('PEMBATALAN');
                    $verifikator = $ket->afterLast('OLEH ');

                    return [
                        'no_jurnal' => $jurnal->no_jurnal,
                        'waktu_jurnal' => $jurnal->waktu_jurnal,
                        'no_rawat' => $jurnal->no_rawat,
                        'no_rkm_medis' => $jurnal->no_rkm_medis,
                        'no_tagihan' => $jurnal->no_tagihan,
                        'kd_pj' => $jurnal->kd_pj,
                        'piutang_dibayar' => $jurnal->besar_cicilan,
                        'tgl_penagihan' => $jurnal->tgl_penagihan,
                        'tgl_jatuh_tempo' => $jurnal->tgl_jatuh_tempo,
                        'tgl_bayar' => $jurnal->tgl_bayar,
                        'status' => $status ? 'Batal Bayar' : 'Bayar',
                        'kd_rek' => $jurnal->kd_rek,
                        'nm_rek' => $jurnal->nama_bank,
                        'nik_penagih' => $jurnal->nip,
                        'nik_menyetujui' => $jurnal->nip_menyetujui,
                        'nik_validasi' => (string) $verifikator,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                });

                static::insert($data->all());
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
            'jurnal' => 'waktu_jurnal',
            'penagihan' => 'tgl_penagihan',
            'bayar' => 'tgl_bayar',
        ][$berdasarkanTgl];

        return $query
            ->with([
                'jurnal:no_jurnal,keterangan',
                'registrasi:no_rawat,umurdaftar,sttsumur',
                'pasien:no_rkm_medis,nm_pasien',
                'penjamin:kd_pj,nama_perusahaan,png_jawab',
                'penagih:nik,nama',
                'penyetuju:nik,nama',
                'pemvalidasi:nik,nama',
            ])
            ->where('kd_rek', $rekening)
            ->whereBetween($filterTgl, [$tglAwal, $tglAkhir]);
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
