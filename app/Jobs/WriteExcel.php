<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesExportSession;
use App\Models\Aplikasi\User;
use App\Models\Export;
use App\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader as CsvReader;
use League\Csv\Statement;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class WriteExcel implements ShouldQueue
{
    use Dispatchable;
    use HandlesExportSession;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 1;

    public $timeout = 3600;

    /**
     * @param  array{
     *     userId: string,
     *     exportSessionId: string,
     *     exportName: string,
     * }  $params
     */
    public function __construct(array $params)
    {
        $this->userId = $params['userId'];
        $this->exportSessionId = $params['exportSessionId'];
        $this->exportName = $params['exportName'];
    }

    public function handle(): void
    {
        $disk = $this->getFileDisk();

        $fileName = now()->format('Y-m-d_H-i-s')."_{$this->userId}_{$this->exportSessionId}_{$this->exportName}.xlsx";

        $writer = app(Writer::class);
        $writer->openToFile($temporaryFile = tempnam(sys_get_temp_dir(), $fileName));

        $csvDelimiter = ',';

        $writeRowsFromFile = function (string $file) use ($csvDelimiter, $disk, $writer): void {
            $csvReader = CsvReader::createFromStream($disk->readStream($file));
            $csvReader->setDelimiter($csvDelimiter);
            $csvResults = (new Statement)->process($csvReader);

            foreach ($csvResults->getRecords() as $row) {
                $writer->addRow(Row::fromValues($row));
            }
        };

        $writeRowsFromFile($this->getFileDirectory().'/headers.csv');

        foreach ($disk->files($this->getFileDirectory()) as $file) {
            if (str($file)->endsWith('headers.csv')) {
                continue;
            }

            if (! str($file)->endsWith('.csv')) {
                continue;
            }

            $writeRowsFromFile($file);
        }

        $writer->close();

        $disk->putFileAs(
            $this->getFileDirectory(),
            new File($temporaryFile),
            $fileName
        );

        unlink($temporaryFile);

        Export::where('id_user', $this->userId)
            ->where('export_session_id', $this->exportSessionId)
            ->where('export_name', $this->exportName)->delete();

        $user = User::findByNRP($this->userId);

        $this->updateSessionStatus('completed');

        Notification::make()
            ->message('Export data is ready for download')
            ->filePath($this->getFileDirectory().'/'.$fileName)
            ->success()
            ->send($user);
    }

    public function getFileDirectory(): string
    {
        return "exports/{$this->userId}/{$this->exportSessionId}";
    }

    public function getFileDisk(): FilesystemAdapter
    {
        return Storage::disk('public');
    }

    public function failed(\Throwable $exception): void
    {
        $user = User::findByNRP($this->userId);

        $this->updateSessionStatus('failed');

        Notification::make()
            ->message('Export data failed')
            ->danger()
            ->send($user);
    }
}
