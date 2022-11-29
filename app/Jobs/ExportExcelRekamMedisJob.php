<?php

namespace App\Jobs;

use App\Exports\RekamMedisExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportExcelRekamMedisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $periodeAwal;
    private $periodeAkhir;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($periodeAwal = null, $periodeAkhir = null)
    {
        $this->periodeAwal = $periodeAwal ?? now()->startOfMonth();
        $this->periodeAkhir = $periodeAkhir ?? now()->endOfMonth();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $now = now()->format('Ymd_His');

        (new RekamMedisExport($this->periodeAwal, $this->periodeAkhir))
            ->store("excel/{$now}_rekammedis.xlsx", 'public');
    }
}
