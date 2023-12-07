<?php

namespace App\Livewire\Concerns;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Rizky92\Xlswriter\ExcelExport;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait ExcelExportable
{
    /**
     * List of invalid characters used in Excel Sheet
     * 
     * @var string[]
     */
    private $invalidSheetCharacters = [
        '\\', '/', '?', '*', ':', '[', ']',
    ];

    public function initializeExcelExportable(): void
    {
        $this->listeners = array_merge($this->listeners, [
            'exportToExcel',
            'beginExcelExport',
        ]);
    }

    abstract protected function dataPerSheet(): array;

    abstract protected function columnHeaders(): array;

    protected function pageHeaders(): array
    {
        return [];
    }

    /**
     * @return void
     * 
     * @throws \RuntimeException
     */
    protected function validateSheetNames(): void
    {
        $invalidSheet = collect(array_keys($this->dataPerSheet()))
            ->contains(fn (string $v): bool => Str::containsAll($v, $this->invalidSheetCharacters));

        throw_if($invalidSheet, 'RuntimeException', sprintf("Invalid characters found in sheet: '%s'", (string) $invalidSheet));
    }

    public function exportToExcel(): void
    {
        $this->emit('flash.info', 'Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->validateSheetNames();

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport(): ?StreamedResponse
    {
        $filename = now()->format('Ymd_His') . '_';

        $filename .= method_exists($this, 'filename')
            ? str($this->filename())->trim()->snake()->value()
            : Str::snake(class_basename($this));

        $filename .= '.xlsx';

        $dataSheets = $this->dataPerSheet();

        $firstSheet = array_keys($dataSheets)[0] ?: 'Sheet 1';

        $firstData = Arr::isList($dataSheets)
            ? $dataSheets[0]
            : $dataSheets[$firstSheet];

        $firstData = is_callable($firstData) ? $firstData() : $firstData;

        File::ensureDirectoryExists(storage_path('app/public/excel'));

        $excel = ExcelExport::make($filename, $firstSheet)
            ->setPageHeaders($this->pageHeaders())
            ->setColumnHeaders($this->columnHeaders())
            ->setData($firstData);

        array_shift($dataSheets);

        foreach ($dataSheets as $sheet => $data) {
            if (is_callable($data)) {
                $excel->addSheet($sheet)->setData($data());
            } else {
                $excel->addSheet($sheet)->setData($data);
            }
        }

        return $excel->export();
    }
}
