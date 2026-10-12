<?php

namespace Tests\Fixtures\Livewire;

use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\FlashComponent;
use Livewire\Component;

/**
 * An exporting component with nothing to export: dataPerSheet() returns no
 * sheets at all. That is the state of Riwayat Jurnal Perbaikan and Kirim Hasil
 * MCU Karyawan, whose export was never written, and of any report whose sheets
 * are built from rows that turn out not to exist.
 */
class EmptyExportHarness extends Component
{
    use ExcelExportable;
    use FlashComponent;

    public function render(): string
    {
        return '<div>empty-export-harness</div>';
    }

    protected function dataPerSheet(): array
    {
        return [];
    }
}
