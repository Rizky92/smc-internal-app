<?php

namespace App\Jobs\Keuangan;

use App\Models\Keuangan\BayarPiutang;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\PenagihanPiutang;
use App\Models\Keuangan\PenagihanPiutangDetail;
use App\Models\Keuangan\PiutangPasien;
use App\Models\Keuangan\PiutangPasienDetail;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BayarPiutangPasien implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $noTagihan;
    private string $tglBayar;
    private string $noRawat;
    private string $noRM;
    private string $userId;
    private string $akun;
    private string $akunKontra;
    private float  $nominal;
    private string $akunDiskonPiutang;
    private string $akunTidakTerbayar;
    private string $namaBayar;
    private string $kodePenjamin;

    private float $totalPiutang;
    private float $cicilanSekarang;

    /**
     * Create a new job instance.
     * 
     * @param  array{
     *     no_tagihan: string,
     *     no_rawat: string,
     *     no_rm: string,
     *     tgl_bayar: string,
     *     user_id: string,
     *     akun: string,
     *     akun_kontra: string,
     *     nominal: float,
     *     akun_diskon_piutang: string,
     *     akun_tidak_terbayar: string,
     *     nama_bayar: string,
     *     kd_pj: string
     * } $data
     */
    public function __construct(array $data)
    {
        $this->noTagihan = $data['no_tagihan'];
        $this->tglBayar = $data['tgl_bayar'];
        $this->noRawat = $data['no_rawat'];
        $this->noRM = $data['no_rm'];
        $this->userId = $data['user_id'];
        $this->akun = $data['akun'];
        $this->akunKontra = $data['akun_kontra'];
        $this->nominal = $data['nominal'];
        $this->akunDiskonPiutang = $data['akun_diskon_piutang'];
        $this->akunTidakTerbayar = $data['akun_tidak_terbayar'];
        $this->namaBayar = $data['nama_bayar'];
        $this->kodePenjamin = $data['kd_pj'];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->proceed();
    }

    protected function proceed(): void
    {
        DB::connection('mysql_sik')
            ->transaction(function () {
                tracker_start('mysql_sik');

                BayarPiutang::insert([
                    'tgl_bayar'             => $this->tglBayar,
                    'no_rkm_medis'          => $this->noRM,
                    'catatan'               => sprintf('diverifikasi oleh %s', $this->userId),
                    'no_rawat'              => $this->noRawat,
                    'kd_rek'                => $this->akun,
                    'kd_rek_kontra'         => $this->akunKontra,
                    'besar_cicilan'         => $this->nominal,
                    'diskon_piutang'        => 0,
                    'kd_rek_diskon_piutang' => $this->akunDiskonPiutang,
                    'tidak_terbayar'        => 0,
                    'kd_rek_tidak_terbayar' => $this->akunTidakTerbayar,
                ]);

                Jurnal::catat($this->noRawat, 'U', sprintf('BAYAR PIUTANG TAGIHAN %s, OLEH %s', $this->noTagihan, $this->userId), $this->tglBayar, [
                    ['kd_rek' => $this->akun, 'debet' => $this->nominal, 'kredit' => 0],
                    ['kd_rek' => $this->akunKontra, 'debet' => 0, 'kredit' => $this->nominal],
                ]);

                tracker_end('mysql_sik', $this->userId);
            });

        DB::connection('mysql_sik')
            ->transaction(function () {
                $this->setLunasPiutang();
            });

        DB::connection('mysql_sik')
            ->transaction(function () {
                $this->setSelesaiPenagihanPiutang();
            });
    }

    /**
     * @template T of \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    protected function setLunasPiutang(): void
    {
        /** @var \Closure(T): T */
        $query = fn ($q) => $q->where([
            ['nama_bayar', '=', $this->namaBayar],
            ['kd_pj', '=', $this->kodePenjamin],
        ]);

        $piutangPasien = PiutangPasien::query()
            ->with(['detail' => $query])
            ->where('no_rawat', $this->noRawat)
            ->whereHas('detail', $query)
            ->first();

        $this->totalPiutang = PiutangPasienDetail::query()
            ->where('no_rawat', $this->noRawat)
            ->sum('sisapiutang');

        $this->totalPiutang = intval(round(floatval($this->totalPiutang)));

        $this->cicilanSekarang = BayarPiutang::query()
            ->where('no_rawat', $this->noRawat)
            ->where('no_rkm_medis', $this->noRM)
            ->sum(DB::raw('besar_cicilan + diskon_piutang + tidak_terbayar'));

        $this->cicilanSekarang = intval(round(floatval($this->cicilanSekarang)));

        if (is_null($piutangPasien) || ($this->totalPiutang - $this->cicilanSekarang) > 0) {
            return;
        }

        tracker_start('mysql_sik');

        $piutangPasien->update(['status' => 'Lunas']);

        tracker_end('mysql_sik', $this->userId);
    }

    protected function setSelesaiPenagihanPiutang(): void
    {
        $tagihanPiutang = PenagihanPiutang::query()
            ->with('detail')
            ->where('no_tagihan', $this->noTagihan)
            ->first();
            
        if (is_null($tagihanPiutang)) {
            return;
        }

        $totalTagihanPiutang = $tagihanPiutang->detail->sum('sisapiutang');
        $totalTagihanPiutang = intval(round(floatval($totalTagihanPiutang)));

        $piutangDibayar = BayarPiutang::query()
            ->whereIn('no_rawat', $tagihanPiutang->detail->pluck('no_rawat')->all())
            ->where('kd_rek', $this->akun)
            ->where('kd_rek_kontra', $this->akunKontra)
            ->sum(DB::raw('besar_cicilan + diskon_piutang + tidak_terbayar'));

        $piutangDibayar = intval(round(floatval($piutangDibayar)));

        dump($totalTagihanPiutang, $piutangDibayar);

        if ($totalTagihanPiutang !== $piutangDibayar) {
            return;
        }

        tracker_start('mysql_sik');

        $tagihanPiutang->update(['status' => 'Sudah Dibayar']);

        tracker_end('mysql_sik', $this->userId);
    }
}
