<?php

namespace Tests\Fixtures\Livewire;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use Livewire\Component;

/**
 * A modal without Filterable, so hideModal()'s method_exists() guard is the
 * branch under test rather than the branch skipped.
 */
class PlainModalHarness extends Component
{
    use DeferredModal;
    use FlashComponent;

    public function render(): string
    {
        return '<div>plain-modal-harness</div>';
    }
}
