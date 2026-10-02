<?php

namespace App\Livewire\Pages\Akreditasi\Modal;

use App\Application\Akreditasi\Actions\SaveProofMethodAction;
use App\Application\Akreditasi\DTOs\ProofMethodData;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Akreditasi\ProofMethod;
use Illuminate\View\View;
use Livewire\Component;

class InputProofMethod extends Component
{
    use DeferredModal;
    use FlashComponent;

    public ?int $proofMethodId = null;

    public string $kode = '';

    public string $nama = '';

    protected $listeners = [
        'prepare'                               => 'loadProofMethod',
        'input-proof-method.hide-modal'         => 'hideModal',
        'input-proof-method.show-modal'         => 'showModal',
    ];

    public function loadProofMethod(?int $id = null): void
    {
        $this->resetExcept([]);

        if ($id) {
            $proofMethod = ProofMethod::findOrFail($id);
            $this->proofMethodId = $proofMethod->id;
            $this->kode = $proofMethod->kode;
            $this->nama = $proofMethod->nama;
        }

        $this->isDeferred = false;
        $this->dispatchBrowserEvent('input-proof-method.show-modal');
    }

    public function save(SaveProofMethodAction $action): void
    {
        $data = ProofMethodData::from([
            'id'   => $this->proofMethodId,
            'kode' => $this->kode,
            'nama' => $this->nama,
        ]);

        $action->execute($data);

        $this->emit('flash.success', 'Metode Pembuktian berhasil disimpan.');
        $this->emit('proof-method-saved');
        $this->hideModal();
    }

    public function hideModal(): void
    {
        $this->isDeferred = true;
        $this->dispatchBrowserEvent('input-proof-method.hide-modal');
    }

    public function render(): View
    {
        return view('livewire.pages.akreditasi.modal.input-proof-method');
    }
}
