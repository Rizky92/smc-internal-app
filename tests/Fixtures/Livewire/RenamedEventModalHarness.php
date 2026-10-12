<?php

namespace Tests\Fixtures\Livewire;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * A modal that overrides showModal() and renames the event it answers to, which
 * is what the four modals under app/Livewire/Pages/User do.
 *
 * Overriding the method replaces the trait's #[On('showModal')] along with it —
 * the attribute lives on the method, not on the trait — so the override has to
 * carry its own. Three modals on the Manajemen User page once did not, and
 * opened empty for every user until the missing attributes were added back.
 */
class RenamedEventModalHarness extends Component
{
    use DeferredModal;
    use FlashComponent;

    /** @var bool */
    public $dimuat = false;

    #[On('harness.show-custom')]
    public function showModal(): void
    {
        $this->isDeferred = false;
        $this->dimuat = true;

        $this->dispatch('modal-loaded');
    }

    public function render(): string
    {
        return '<div>renamed-event-modal-harness</div>';
    }
}
