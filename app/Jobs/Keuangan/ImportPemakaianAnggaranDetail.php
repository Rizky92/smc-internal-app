<?php

namespace App\Jobs\Keuangan;

use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemNotFoundException;
use Spatie\SimpleExcel\SimpleExcelReader;

class ImportPemakaianAnggaranDetail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private string $keterangan;

    private string $tglPakai;

    private int $anggaranBidangId;

    private string $fileImport;

    /** @var array<array-key, array{keterangan: string, nominal: numeric}> */
    private array $detail;

    private string $userId;

    /**
     * @param array{
     *      keterangan: string,
     *      tglPakai: string,
     *      anggaranBidangId: int,
     *      fileImport: \Livewire\TemporaryUploadedFile,
     *      detail: array<array-key, array{keterangan: string, nominal: numeric}>,
     *      userId: string,
     * } $params
     */
    public function __construct(array $params)
    {
        $path = $params['fileImport']->store('temp');

        if ($path === false) {
            throw new FilesystemNotFoundException('Temporary file not available');
        }

        $this->keterangan = $params['keterangan'];
        $this->tglPakai = $params['tglPakai'];
        $this->anggaranBidangId = $params['anggaranBidangId'];
        $this->fileImport = $path;
        $this->detail = $params['detail'];
        $this->userId = $params['userId'];
    }

    public function handle(): void
    {
        if (! $this->fileImport) {
            throw new FilesystemNotFoundException('Temporary file not available');
        }

        $this->proceed();

        Storage::delete($this->fileImport);
    }

    protected function proceed(): void
    {
        DB::connection('mysql_smc')->transaction(function () {
            $pemakaianAnggaran = PemakaianAnggaran::create([
                'judul'              => $this->keterangan,
                'tgl_dipakai'        => $this->tglPakai,
                'anggaran_bidang_id' => $this->anggaranBidangId,
                'user_id'            => $this->userId,
            ]);

            $details = collect();

            SimpleExcelReader::create(storage_path('app/'.$this->fileImport))
                ->getRows()
                ->take(3000)
                ->each(fn (array $row) => $details->push($row));

            if ($details->isNotEmpty()) {
                $pemakaianAnggaran->detail()->createMany($details);
            }
        });
    }
}
