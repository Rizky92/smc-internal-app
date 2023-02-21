<?php

namespace App\Support\Traits\Livewire;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Rizky92\Xlswriter\ExcelExport;

trait ExcelExportable
{
    public function initializeExcelExportable()
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

    public function exportToExcel()
    {
        if (method_exists($this, 'flashInfo')) {
            $this->emit('flash.info', 'Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');
        }

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport()
    {
        $filename = now()->format('Ymd_His') . '_';

        if (method_exists($this, 'filename')) {
            $filename .= Str::of($this->filename())
                ->trim()
                ->snake();
        } else {
            $filename .= Str::snake(class_basename($this));
        }

        $filename .= '.xlsx';

        $dataSheets = $this->dataPerSheet();

        $firstSheet = sizeof($dataSheets) > 1
            ? (string) array_keys($dataSheets)[0]
            : 'Sheet 1';

        $firstData = Arr::isList($dataSheets)
            ? $dataSheets[0]
            : $dataSheets[$firstSheet];

        $excel = ExcelExport::make($filename, $firstSheet)
            ->setPageHeaders($this->pageHeaders())
            ->setColumnHeaders($this->columnHeaders())
            ->setData($firstData);
        
        array_shift($dataSheets);

        foreach ($dataSheets as $sheet => $data) {
            $excel
                ->addSheet($sheet)
                ->setData($data);
        }

        return $excel->export();
    }
}
