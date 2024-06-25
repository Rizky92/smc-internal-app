<?php

namespace App\Jobs\Keuangan;

use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\Models\Keuangan\RKAT\PemakaianAnggaranDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelReader;

class ImportPemakaianAnggaranDetail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue; 
    use Queueable;
    use SerializesModels;

    private $keterangan;

    private $tglPakai;

    private $anggaranBidangId;

    private $fileImport;

    private $detail;

    private $userId;

    /**
     * Create a new job instance.
     * 
     * @param array{
     *      keterangan: string,
     *      tglPakai: string,
     *      anggaranBidangId: int,
     *      fileImport: uploaded file,
     *      detail: array 
     *      userId: string
     * } $params
     */
    public function __construct(array $params)
    {
        $this->keterangan = $params['keterangan'];
        $this->tglPakai = $params['tglPakai'];
        $this->anggaranBidangId = $params['anggaranBidangId'];
        $this->fileImport = $params['fileImport']->store('temp');
        $this->detail = $params['detail'];
        $this->userId = $params['userId'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->proceed();

        Storage::delete($this->fileImport);
    }

    protected function proceed(): void
    {
        $pemakaianAnggaran = PemakaianAnggaran::create([
            'judul' => $this->keterangan,
            'tgl_dipakai' => $this->tglPakai,
            'anggaran_bidang_id' => $this->anggaranBidangId,
            'user_id' => $this->userId,
        ]);
    
        // Use the stored path to access the file
        $path = storage_path('app/' . $this->fileImport); // Adjust the path based on where you stored the file
        $rows = SimpleExcelReader::create($path)->getRows()->take(3000);
    
        $details = [];
        $rows->each(function ($row) use (&$details) {
            $details[] = [
                'keterangan' => $row['keterangan'] ?? '',
                'nominal' => $row['nominal'] ?? 0,
            ];
        });
    
        $pemakaianAnggaran->detail()->createMany($details);
    }
}
