<?php

namespace Tests\Fixtures\Livewire;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use Livewire\Component;

/**
 * The shape every report page in SIAP has, reduced to nothing but the shared
 * traits.
 *
 * Six of the nine traits in app/Livewire/Concerns are inherited by 70-92 page
 * components each, and the behaviour they contribute is identical everywhere.
 * Testing it through a real page would drag in that page's Khanza queries and
 * permissions, and would only ever prove the traits work for that one page.
 * This harness is the trait contract with the reporting removed, so a failure
 * names the trait rather than whichever page happened to catch it.
 *
 * The trait list and its order mirror stubs/livewire.stub deliberately: that
 * stub is what `artisan make:livewire` writes, so it is the arrangement every
 * page actually runs.
 */
class ReportHarness extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * A legacy computed property, present so fullRefresh() has something to
     * unset. Livewire 3 memoises these per request, and Filterable::fullRefresh()
     * relies on deriving the property name back from the method name.
     */
    public function getBarisProperty(): array
    {
        return ['satu', 'dua'];
    }

    /**
     * <x-flash /> sits at the top of every page view in the application (see
     * stubs/livewire.view.stub), and it is the only thing that reads what
     * FlashComponent writes. Keeping it here lets a test assert the banner a
     * user would actually see rather than the session keys behind it.
     */
    public function render(): string
    {
        return '<div><x-flash />report-harness</div>';
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            'Laporan' => [
                ['A1', 'B1'],
                ['A2', 'B2'],
            ],
        ];
    }

    protected function columnHeaders(): array
    {
        return ['Kolom A', 'Kolom B'];
    }

    protected function pageHeaders(): array
    {
        return ['Harness'];
    }
}
