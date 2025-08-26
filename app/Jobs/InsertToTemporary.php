<?php

namespace App\Jobs;

use App\Models\Keuangan\Jurnal\Jurnal;
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

    public function __construct(
        protected string $userId,
        protected string $exportSessionId,
        protected string $tglAwal,
        protected string $tglAkhir,
        protected string $kodeRekening,
    ) {}

    public function handle()
    {
        DB::statement(DB::raw('SET @rownum = 0'));

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
            'column9'
        ], $query->toBase());
    }
}