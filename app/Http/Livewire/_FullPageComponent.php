<?php

namespace App\Http\Livewire;

use App\Support\Traits\Livewire\FlashComponent;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Rizky92\Xlswriter\ExcelExport;

abstract class _FullPageComponent extends Component
{
    use WithPagination, FlashComponent;

    public $cari;

    public $perpage;

    public $periodeAwal;

    public $periodeAkhir;

    protected function defaultQueryStrings()
    {
        return [
            'cari' => [
                'except' => '',
            ],
            'perpage' => [
                'except' => 25,
            ],
            'periodeAwal' => [
                'except' => now()->startOfMonth()->format('Y-m-d'),
                'as' => 'periode_awal',
            ],
            'periodeAkhir' => [
                'except' => now()->endOfMonth()->format('Y-m-d'),
                'as' => 'periode_akhir',
            ],
        ];
    }

    protected function defaultListeners()
    {
        return [
            'beginExcelExport',
            'searchData',
            'resetFilters',
            'fullRefresh',
        ];
    }

    abstract public function render();

    abstract public function defaultPropertyValues();

    abstract public function getColumnHeaders(): array;

    abstract public function getTitle(): string;

    abstract public function getExcelData(): mixed;

    public function exportToExcel()
    {
        $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $fileTitle = Str::snake($this->getTitle());

        $filename = "{$timestamp}_{$fileTitle}";

        $titles = [
            'RS Samarinda Medika Citra',
            $this->getTitle(),
            now()->format('d F Y'),
        ];

        $excel = ExcelExport::make($filename)
            ->setPageHeaders($titles)
            ->setColumnHeaders($this->getColumnHeaders())
            ->setData($this->getExcelData());

        return $excel->export();
    }

    public function searchData()
    {
        $this->resetPage();

        $this->emit('$refresh');
    }

    public function resetFilters()
    {
        $this->defaultPropertyValues();

        $this->searchData();
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
