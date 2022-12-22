<?php

namespace App\Support\Traits\Livewire;

use Illuminate\Support\Facades\Storage;
use LogicException;
use Vtiful\Kernel\Excel;

trait ExportToExcel
{
    public function notifyComponent()
    {
        if (method_exists($this, 'flashInfo')) {
            $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');
        }

        $this->emit('initializeExport');
    }

    public function initializeExport()
    {
        if (! method_exists($this, 'getExcelColumnHeaders')) {
            throw new LogicException('Column headers are not defined!');
        }

        if (! method_exists($this, 'getExcelData')) {
            throw new LogicException('No data has been set to export excel');
        }

        $columnHeaders = $this->getExcelColumnHeaders();

        $timestamp = now()->format('Ymd_His');

        $filename = "excel/{$timestamp}.xlsx";

        if (method_exists($this, 'getFilename')) {
            $filename = $this->getFilename();
        }

        $config = [
            'path' => storage_path('app/public'),
        ];

        $data = $this->getExcelData();

        (new Excel($config))
            ->fileName($filename)
            ->header($columnHeaders)
            ->data($data)
            ->output();

        return Storage::disk('public')->download($filename);
    }
}