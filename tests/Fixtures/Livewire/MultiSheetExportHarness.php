<?php

namespace Tests\Fixtures\Livewire;

use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\FlashComponent;
use Livewire\Component;

/**
 * The three parts of ExcelExportable that ReportHarness does not reach:
 * more than one sheet, column headers keyed per sheet, and sheet data supplied
 * as a closure so the rows are only built when that sheet is written.
 *
 * It also defines filename(), the trait's override point. No page component in
 * app/Livewire/Pages defines one today, so this fixture is the only thing
 * holding that branch to its contract.
 */
class MultiSheetExportHarness extends Component
{
    use ExcelExportable;
    use FlashComponent;

    /**
     * Set when the closure for the second sheet runs, so a test can tell
     * whether the rows were built lazily or up front.
     *
     * @var bool
     */
    public $sheetKeduaDibangun = false;

    public function render(): string
    {
        return '<div>multi-sheet-harness</div>';
    }

    protected function filename(): string
    {
        return '  Laporan Buku Besar  ';
    }

    protected function dataPerSheet(): array
    {
        return [
            'Ringkasan' => [
                ['R1', 'R2'],
            ],
            'Rincian' => function (): array {
                $this->sheetKeduaDibangun = true;

                return [
                    ['D1', 'D2', 'D3'],
                ];
            },
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Ringkasan' => ['Akun', 'Saldo'],
            'Rincian'   => ['Tanggal', 'Keterangan', 'Nominal'],
        ];
    }
}
