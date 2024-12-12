<?php

namespace App\Jobs\Exporter\Keuangan;

use AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade;
use App\Models\Keuangan\Jurnal\Jurnal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class BukuBesarExporter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $batchId;

    protected $filters;

    protected $userId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($batchId, $filters, $userId)
    {
        $this->batchId = $batchId;
        $this->filters = $filters;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $query = Jurnal::on('mysql_sik')
            ->select(DB::raw("'$this->batchId' as batch_id"),
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
            ->join('sik_20240718.detailjurnal', 'jurnal.no_jurnal', '=', 'detailjurnal.no_jurnal')
            ->join('sik_20240718.rekening', 'detailjurnal.kd_rek', '=', 'rekening.kd_rek')
            ->when(! empty($this->filters['kodeRekening']), fn(Builder $q) => $q->where('detailjurnal.kd_rek', $this->filters['kodeRekening']))
            ->whereBetween('jurnal.tgl_jurnal', [$this->filters['tglAwal'], $this->filters['tglAkhir']])
            ->where(function ($query) {
                $query->where('jurnal.no_jurnal', 'like', '%' . $this->filters['cari'] . '%')
                    ->orWhere('jurnal.no_bukti', 'like', '%' . $this->filters['cari'] . '%')
                    ->orWhere('jurnal.keterangan', 'like', '%' . $this->filters['cari'] . '%')
                    ->orWhere('detailjurnal.kd_rek', 'like', '%' . $this->filters['cari'] . '%')
                    ->orWhere('rekening.nm_rek', 'like', '%' . $this->filters['cari'] . '%');
            });

        DB::connection('mysql_smc')->table('temporaries')->insertUsing(
            ['batch_id', 'column1', 'column2', 'column3', 'column4', 'column5', 'column8', 'column9', 'column10', 'column11'],
            $query->toBase()
        );
    }
}
