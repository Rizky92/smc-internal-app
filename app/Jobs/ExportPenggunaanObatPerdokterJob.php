<?php

namespace App\Jobs;

use App\Exports\Farmasi\PenggunaanObatPerdokterExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportPenggunaanObatPerdokterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $periodeAwal;
    private $periodeAkhir;
    private $timestamp;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $periodeAwal, string $periodeAkhir, string $timestamp)
    {
        $this->periodeAwal = $periodeAwal;
        $this->periodeAkhir = $periodeAkhir;
        $this->timestamp = $timestamp;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $filename = "excel/{$this->timestamp}_obat_perdokter.xlsx";

        (new PenggunaanObatPerdokterExport($this->periodeAwal, $this->periodeAkhir))
            ->store($filename, 'public');
    }
}
