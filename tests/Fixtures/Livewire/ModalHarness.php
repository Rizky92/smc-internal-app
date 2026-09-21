<?php

namespace Tests\Fixtures\Livewire;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use Livewire\Component;

/**
 * A modal that also filters — the arrangement the 19 DeferredModal users have.
 *
 * DeferredModal::hideModal() calls resetFilters() only when the component has
 * it. That guard is what is supposed to stop a modal reopening with the
 * previous row's state still loaded, which is the bug class the Livewire 3
 * migration hit repeatedly (InputPintu and the five modals that shared its
 * shape). PlainModalHarness covers the other side of the guard.
 */
class ModalHarness extends Component
{
    use DeferredModal;
    use Filterable;
    use FlashComponent;
    use LiveTable;

    /** @var string */
    public $namaBidang;

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): string
    {
        return '<div>modal-harness</div>';
    }

    protected function defaultValues(): void
    {
        $this->namaBidang = '';
    }
}
