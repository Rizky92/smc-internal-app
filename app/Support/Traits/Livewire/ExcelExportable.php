<?php

namespace App\Support\Traits\Livewire;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
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

    protected function validateSheetNames(): void
    {
        $invalidSheet = collect(array_keys($this->dataPerSheet()))
            ->filter(fn (string $v): bool => Str::containsAll($v, $this->invalidSheetCharacters))
            ->first();

        throw_if(!is_null($invalidSheet), 'RuntimeException', sprintf("Invalid characters found in sheet: '%s'", (string) $invalidSheet));
    }

    public function exportToExcel(): void
    {
        $this->emit('flash.info', 'Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');
        
        // Validasi sebelum proses export dimulai
        $this->validateSheetNames();

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport(): StreamedResponse
    {
        $filename = now()->format('Ymd_His') . '_';

        $filename .= method_exists($this, 'filename')
            ? Str::of($this->filename())->trim()->snake()
            : Str::snake(class_basename($this));

        $filename .= '.xlsx';

        $dataSheets = $this->dataPerSheet();

        $firstSheet = array_keys($dataSheets)[0] ?: 'Sheet 1';

        $firstData = Arr::isList($dataSheets)
            ? $dataSheets[0]
            : $dataSheets[$firstSheet];

        $firstData = is_callable($firstData) ? $firstData() : $firstData;

        $excel = ExcelExport::make($filename, $firstSheet)
            ->setPageHeaders($this->pageHeaders())
            ->setColumnHeaders($this->columnHeaders())
            ->setData($firstData);
        
        array_shift($dataSheets);

        foreach ($dataSheets as $sheet => $data) {
            if ($data instanceof Closure || is_callable($data)) {
                $excel->addSheet($sheet)->setData($data());
            } else {
                $excel->addSheet($sheet)->setData($data);
            }
        }

        return $excel->export();
    }
}
