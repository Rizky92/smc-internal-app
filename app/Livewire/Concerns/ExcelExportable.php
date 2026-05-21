<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\LazyCollection;
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

    /**
     * @return array<array-key, (\Closure(): \Illuminate\Database\Eloquent\Collection|Collection|LazyCollection|array)|Collection|Collection|array>
     */
    abstract protected function dataPerSheet(): array;

    /**
     * @return array<array-key, string[]|string>
     */
    protected function columnHeaders(): array
    {
        return [];
    }

    protected function pageHeaders(): array
    {
        return [];
    }

    /**
     * @throws \RuntimeException
     */
    protected function validateSheetNames(): array
    {
        $dataSheets = $this->dataPerSheet();

        $invalidSheet = collect(array_keys($dataSheets))
            ->contains(fn (string $v): bool => str()->containsAll($v, $this->invalidSheetCharacters));

        throw_if($invalidSheet, 'RuntimeException', sprintf("Invalid characters found in sheet: '%s'", (string) $invalidSheet));

        return $dataSheets;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function sanitizeExcelValue($value)
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = str_replace(
            [
                '–', // en dash
                '—', // em dash
                '‘',
                '’',
                '“',
                '”',
            ],
            [
                '-',
                '-',
                "'",
                "'",
                '"',
                '"',
            ],
            $value
        );

        $value = preg_replace(
            '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u',
            '',
            $value
        );

        return trim($value);
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    protected function sanitizeExcelData($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeExcelData'], $data);
        }

        if ($data instanceof Collection || $data instanceof LazyCollection) {
            return $data->map(function ($row) {
                return $this->sanitizeExcelData($row);
            });
        }

        return $this->sanitizeExcelValue($data);
    }

    public function exportToExcel(): void
    {
        $this->emit('flash.info', 'Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport(): ?StreamedResponse
    {
        $filename = now()->format('Ymd_His').'_';

        $filename .= method_exists($this, 'filename')
            ? str($this->filename())->trim()->snake()->value()
            : str()->snake(class_basename($this));

        $filename .= '.xlsx';

        $dataSheets = $this->validateSheetNames();
        $columnHeaders = $this->columnHeaders();

        $firstSheet = array_keys($dataSheets)[0] ?: 'Sheet 1';

        $firstData = Arr::isList($dataSheets)
            ? $dataSheets[0]
            : $dataSheets[$firstSheet];

        $firstData = is_callable($firstData) ? $firstData() : $firstData;

        $firstData = $this->sanitizeExcelData($firstData);

        File::ensureDirectoryExists(storage_path('app/public/excel'));

        $excel = ExcelExport::make($filename, $firstSheet)
            ->setPageHeaders($this->pageHeaders());

        if (Arr::isAssoc($columnHeaders)) {
            $excel->setColumnHeaders($columnHeaders[$firstSheet] ?? []);
        } else {
            $excel->setColumnHeaders($columnHeaders);
        }

        $excel->setData($firstData);

        array_shift($dataSheets);

        foreach ($dataSheets as $sheet => $data) {
            $data = is_callable($data) ? $data() : $data;

            $data = $this->sanitizeExcelData($data);

            $excel->addSheet($sheet);

            if (Arr::isAssoc($columnHeaders)) {
                $excel->setColumnHeaders($columnHeaders[$sheet] ?? []);
            }

            $excel->setData($data);
        }

        $dataSheets = null;

        return $excel->export();
    }
}
