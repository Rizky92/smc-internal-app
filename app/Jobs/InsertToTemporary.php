<?php

namespace App\Jobs;

use App\Models\Keuangan\Jurnal\Jurnal;
use App\Services\Export\ExportCleanupService;
use App\Services\Export\ExportNotificationService;
use App\Services\Export\ExportSessionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class InsertToTemporary implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 3600;

    private string $userId;

    private string $exportSessionId;

    private string $tglAwal;

    private string $tglAkhir;

    private string $kodeRekening;

    /**
     * @param  array{
     *      userId: string,
     *      exportSessionId: string,
     *      tglAwal: string,
     *      tglAkhir: string,
     *      kodeRekening: string,
     * }  $params
     */
    public function __construct(
        array $params
    ) {
        $this->userId = $params['userId'];
        $this->exportSessionId = $params['exportSessionId'];
        $this->tglAwal = $params['tglAwal'];
        $this->tglAkhir = $params['tglAkhir'];
        $this->kodeRekening = $params['kodeRekening'] ?? '';
    }

    public function handle(): void
    {
        $this->resolveSessionService()->markInserting();

        $query = Jurnal::on('mysql_sik')
            ->select(DB::raw("'$this->exportSessionId' as export_session_id"),
                DB::raw("'$this->userId' as id_user"),
                'jurnal.tgl_jurnal',
                'jurnal.jam_jurnal',
                'jurnal.no_jurnal',
                'jurnal.no_bukti',
                'jurnal.keterangan',
                'detailjurnal.kd_rek',
                'rekening.nm_rek',
                'detailjurnal.debet',
                'detailjurnal.kredit'
            )
            ->join('sik.detailjurnal', 'jurnal.no_jurnal', '=', 'detailjurnal.no_jurnal')
            ->join('sik.rekening', 'detailjurnal.kd_rek', '=', 'rekening.kd_rek')
            ->when(! empty($this->kodeRekening), fn (Builder $q) => $q->where('detailjurnal.kd_rek', $this->kodeRekening))
            ->whereBetween('jurnal.tgl_jurnal', [$this->tglAwal, $this->tglAkhir])
            ->orderBy('jurnal.tgl_jurnal', 'asc')
            ->orderBy('jurnal.jam_jurnal', 'asc')
            ->orderBy('jurnal.no_jurnal', 'asc');

        DB::connection('mysql_smc')->table('exports')->insertUsing([
            'export_session_id',
            'id_user',
            'column1',
            'column2',
            'column3',
            'column4',
            'column5',
            'column6',
            'column7',
            'column8',
            'column9',
        ], $query->toBase());
    }

    public function failed(\Throwable $exception): void
    {
        $this->resolveSessionService()->markFailed($exception->getMessage());
        $this->resolveCleanupService()->cleanDatabase();
        $this->resolveNotificationService()->notifyFailed();
    }

    protected function resolveCleanupService(): ExportCleanupService
    {
        return new ExportCleanupService($this->userId, $this->exportSessionId);
    }

    protected function resolveNotificationService(): ExportNotificationService
    {
        return new ExportNotificationService($this->userId);
    }

    protected function resolveSessionService(): ExportSessionService
    {
        return new ExportSessionService($this->userId, $this->exportSessionId);
    }
}
