<?php

namespace App\Jobs\Keuangan;

use App\Models\Keuangan\BayarPiutang;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\PenagihanPiutang;
use App\Models\Keuangan\PiutangDilunaskan;
use App\Models\Keuangan\PiutangPasien;
use App\Models\Keuangan\PiutangPasienDetail;
use App\Models\Keuangan\Rekening;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class BayarPiutangPasien implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Jurnal $jurnal;

    private string $noTagihan;

    private string $kodePJ;

    private string $noRawat;

    private string $tglBayar;

    private string $userId;

    private string $akunBayar;

    private float $diskonPiutang;

    private string $akunDiskonPiutang;

    private float $tidakTerbayar;

    private string $akunTidakTerbayar;

    /**
     * Create a new job instance.
     *
     * @param  array{
     *     key: string,
     *     tgl_bayar: string,
     *     user_id: string,
     *     akun: string,
     *     diskon_piutang: float,
     *     akun_diskon_piutang: string,
     *     tidak_terbayar: float,
     *     akun_tidak_terbayar: string,
     * }  $params
     */
    public function __construct(array $params)
    {
        [$this->noTagihan, $this->kodePJ, $this->noRawat] = explode('_', $params['key']);
        $this->userId = $params['user_id'];
        $this->tglBayar = $params['tgl_bayar'];
        $this->akunBayar = $params['akun'];
        $this->diskonPiutang = $params['diskon_piutang'] ?? 0;
        $this->akunDiskonPiutang = $params['akun_diskon_piutang'];
        $this->tidakTerbayar = $params['tidak_terbayar'] ?? 0;
        $this->akunTidakTerbayar = $params['akun_tidak_terbayar'];
    }

    public function handle(): void
    {
        $this->proceed();
    }

    protected function proceed(): void
    {
        $model = PenagihanPiutang::query()
            ->accountReceivableByNoRawat($this->noTagihan, $this->kodePJ, $this->noRawat)
            ->first();

        if (is_null($model)) {
            return;
        }

        $sisaCicilan = $totalCicilan = $model->sisapiutang;

        $detailJurnal = collect();

        if ($this->diskonPiutang > 0) {
            $this->diskonPiutang = clamp($this->diskonPiutang, 0, $sisaCicilan);
            $sisaCicilan -= $this->diskonPiutang;
            $detailJurnal->push(['kd_rek' => $this->akunDiskonPiutang, 'debet' => $this->diskonPiutang, 'kredit' => 0]);
        }

        if ($this->tidakTerbayar > 0) {
            $this->tidakTerbayar = clamp($this->tidakTerbayar, 0, $sisaCicilan);
            $sisaCicilan -= $this->tidakTerbayar;
            $detailJurnal->push(['kd_rek' => $this->akunTidakTerbayar, 'debet' => $this->tidakTerbayar, 'kredit' => 0]);
        }

        $detailJurnal->push(
            ['kd_rek' => $this->akunBayar, 'debet' => $sisaCicilan, 'kredit' => 0],
            ['kd_rek' => $model->kd_rek, 'debet' => 0, 'kredit' => $totalCicilan],
        );

        DB::connection('mysql_sik')
            ->transaction(function () use ($model, $totalCicilan, $sisaCicilan) {
                tracker_start('mysql_sik');

                BayarPiutang::insert([
                    'tgl_bayar'             => $this->tglBayar,
                    'no_rkm_medis'          => $model->no_rkm_medis,
                    'catatan'               => sprintf('diverifikasi oleh %s', $this->userId),
                    'no_rawat'              => $this->noRawat,
                    'kd_rek'                => $this->akunBayar,
                    'kd_rek_kontra'         => $model->kd_rek,
                    'besar_cicilan'         => $sisaCicilan,
                    'diskon_piutang'        => $this->diskonPiutang,
                    'kd_rek_diskon_piutang' => $this->akunDiskonPiutang,
                    'tidak_terbayar'        => $this->tidakTerbayar,
                    'kd_rek_tidak_terbayar' => $this->akunTidakTerbayar,
                ]);

                $sukses = DB::connection('mysql_sik')
                    ->statement('update `detail_piutang_pasien` set `sisapiutang` = `sisapiutang` - ? where `no_rawat` = ? and `nama_bayar` = ? and `kd_pj` = ?', [
                        $totalCicilan, $this->noRawat, $model->nama_bayar, $model->kd_pj,
                    ]);

                tracker_end('mysql_sik', $this->userId);

                $this->setLunasPiutang();

                $this->setSelesaiPenagihanPiutang($model->kd_rek);

                tracker_start('mysql_sik');

                $this->jurnal = Jurnal::catat(
                    $this->noRawat,
                    sprintf('BAYAR PIUTANG TAGIHAN %s, OLEH %s', $this->noTagihan, $this->userId),
                    $this->tglBayar
                );

                tracker_end('mysql_sik', $this->userId);
            });

        tracker_start('mysql_sik');

        $this->jurnal->isiDetail($detailJurnal);

        tracker_end('mysql_sik', $this->userId);

        $this->jurnal->load('detail');

        $this->masukkanKeJurnalPiutangLunas(
            $model->no_rkm_medis,
            $model->sisapiutang,
            $model->tanggal,
            $model->tanggaltempo,
            $model->nip,
            $model->nip_menyetujui
        );
    }

    protected function setLunasPiutang(): void
    {
        $sisaPiutang = round(PiutangPasienDetail::query()
            ->where('no_rawat', $this->noRawat)
            ->sum('sisapiutang'));

        if ((int) $sisaPiutang <= 0) {
            tracker_start('mysql_sik');

            PiutangPasien::query()
                ->where('no_rawat', $this->noRawat)
                ->update(['status' => 'Lunas']);

            tracker_end('mysql_sik', $this->userId);
        }
    }

    protected function setSelesaiPenagihanPiutang(string $akunKontra): void
    {
        $tagihanPiutang = PenagihanPiutang::query()
            ->with('detail')
            ->where('no_tagihan', $this->noTagihan)
            ->first();

        if (is_null($tagihanPiutang)) {
            return;
        }

        $totalTagihanPiutang = round($tagihanPiutang->detail->sum('sisapiutang'));

        $piutangDibayar = BayarPiutang::query()
            ->whereIn('no_rawat', $tagihanPiutang->detail->pluck('no_rawat')->all())
            ->where('kd_rek', $this->akunBayar)
            ->where('kd_rek_kontra', $akunKontra)
            ->sum(DB::raw('besar_cicilan + diskon_piutang + tidak_terbayar'));

        $piutangDibayar = intval(round(floatval($piutangDibayar)));

        if ($totalTagihanPiutang >= $piutangDibayar) {
            return;
        }

        tracker_start('mysql_sik');

        $tagihanPiutang->update(['status' => 'Sudah Dibayar']);

        tracker_end('mysql_sik', $this->userId);
    }

    protected function masukkanKeJurnalPiutangLunas(
        string $noRM,
        float $besarCicilan,
        string $tglTagihan,
        string $tglJatuhTempo,
        string $penagih,
        string $menyetujui
    ): void {
        tracker_start('mysql_smc');

        PiutangDilunaskan::create([
            'no_jurnal'       => $this->jurnal->no_jurnal,
            'waktu_jurnal'    => carbon($this->jurnal->tgl_jurnal)->setTimeFromTimeString($this->jurnal->jam_jurnal),
            'no_rawat'        => $this->noRawat,
            'no_rkm_medis'    => $noRM,
            'no_tagihan'      => $this->noTagihan,
            'kd_pj'           => $this->kodePJ,
            'piutang_dibayar' => $besarCicilan,
            'tgl_penagihan'   => $tglTagihan,
            'tgl_jatuh_tempo' => $tglJatuhTempo,
            'tgl_bayar'       => $this->tglBayar,
            'status'          => 'Bayar',
            'kd_rek'          => $this->akunBayar,
            'nm_rek'          => Rekening::where('kd_rek', $this->akunBayar)->value('nm_rek'),
            'nik_penagih'     => $penagih,
            'nik_menyetujui'  => $menyetujui,
            'nik_validasi'    => $this->userId,
        ]);

        tracker_end('mysql_smc', $this->userId);
    }
}
