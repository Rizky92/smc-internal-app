<?php

namespace Tests\Fixtures\Livewire;

use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\FlashComponent;
use Livewire\Component;

/**
 * An export whose sheet name the test chooses, so ExcelExportable's guard
 * against characters Excel will not accept in a sheet name can be aimed at.
 */
class NamedSheetExportHarness extends Component
{
    use ExcelExportable;
    use FlashComponent;

    /** @var string */
    public $namaSheet = 'Laporan';

    public function render(): string
    {
        return '<div>named-sheet-harness</div>';
    }

    protected function dataPerSheet(): array
    {
        return [
            $this->namaSheet => [
                ['A1', 'B1'],
            ],
        ];
    }

    /**
     * ExcelExport refuses to take data for a sheet that has no column headers,
     * so these are required even though the sheet name is what is under test.
     */
    protected function columnHeaders(): array
    {
        return ['Kolom A', 'Kolom B'];
    }
}
