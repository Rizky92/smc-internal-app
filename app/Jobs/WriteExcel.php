<?php

namespace App\Jobs;

use App\Models\Aplikasi\User;
use App\Notifications\ExportReadyNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader as CsvReader;
use League\Csv\Statement;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class WriteExcel implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected string $userId,
        protected string $exportSessionId,
    ) {}

    public function handle()
    {
        $disk = $this->getFileDisk();

        $fileName = now()->format('Y-m-d_H-i-s') . "_{$this->userId}_{$this->exportSessionId}.xlsx";

        $writer = app(Writer::class);
        $writer->openToFile($temporaryFile = tempnam(sys_get_temp_dir(), $fileName));

        $csvDelimiter = ',';

        $writeRowsFromFile = function (string $file) use ($csvDelimiter, $disk, $writer) {
            $csvReader = CsvReader::createFromStream($disk->readStream($file));
            $csvReader->setDelimiter($csvDelimiter);
            $csvResults = (new Statement)->process($csvReader);

            foreach ($csvResults->getRecords() as $row) {
                $writer->addRow(Row::fromValues($row));
            }
        };

        $writeRowsFromFile($this->getFileDirectory() . '/headers.csv');

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

        $user = User::findByNRP($this->userId);

        Notification::send($user, new ExportReadyNotification($user, $this->getFileDirectory() . '/' . $fileName));
    }

    public function getFileDirectory(): string
    {
        return "exports/{$this->userId}/{$this->exportSessionId}";
    }

    public function getFileDisk(): Filesystem|FilesystemAdapter
    {
        return Storage::disk('local');
    }
}