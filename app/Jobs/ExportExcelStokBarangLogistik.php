<?php

namespace App\Jobs;

use App\Exports\LaporanStokMinmaxBarangLogistik;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportExcelStokBarangLogistik implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $timestamp;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($timestamp = null)
    {
        $this->timestamp = $timestamp ?? now()->format('Ymd_His');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new LaporanStokMinmaxBarangLogistik($this->timestamp))
            ->store("excel/{$this->timestamp}_daruratstok_logistik.xlsx", 'public');
    }
}
