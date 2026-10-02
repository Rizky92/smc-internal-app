<?php

namespace App\Livewire\Pages\Akreditasi\Modal;

use App\Application\Akreditasi\Actions\SaveAssessmentElementAction;
use App\Application\Akreditasi\DTOs\AssessmentElementData;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Akreditasi\AssessmentElement;
use App\Models\Akreditasi\ProofMethod;
use App\Models\Akreditasi\Standard;
use Illuminate\View\View;
use Livewire\Component;

class InputAssessmentElement extends Component
{
    use DeferredModal;
    use FlashComponent;

    public ?int $assessmentElementId = null;

    public string $standardId = '';

    public string $kode = '';

    public string $deskripsi = '';

    public ?string $penjelasanKelengkapanBukti = null;

    public string $proofMethodId = '';

    public int $urutan = 0;

    protected $listeners = [
        'prepare'                                     => 'loadAssessmentElement',
        'input-assessment-element.hide-modal'         => 'hideModal',
        'input-assessment-element.show-modal'         => 'showModal',
    ];

    public function getStandardsProperty(): array
    {
        return Standard::query()
            ->with('focusArea')
            ->orderBy('focus_area_id')
            ->orderBy('urutan')
            ->get()
            ->mapWithKeys(fn ($s) => [$s->id => "[{$s->focusArea->kode}] {$s->kode} - {$s->judul}"])
            ->all();
    }

    public function getProofMethodsProperty(): array
    {
        return ProofMethod::query()->orderBy('kode')->pluck('nama', 'id')->all();
    }

    public function loadAssessmentElement(?int $id = null): void
    {
        $this->resetExcept([]);

        if ($id) {
            $element = AssessmentElement::findOrFail($id);
            $this->assessmentElementId = $element->id;
            $this->standardId = (string) $element->standard_id;
            $this->kode = $element->kode;
            $this->deskripsi = $element->deskripsi;
            $this->penjelasanKelengkapanBukti = $element->penjelasan_kelengkapan_bukti;
            $this->proofMethodId = (string) ($element->proof_method_id ?? '');
            $this->urutan = $element->urutan;
        }

        $this->isDeferred = false;
        $this->dispatchBrowserEvent('input-assessment-element.show-modal');
    }

    public function save(SaveAssessmentElementAction $action): void
    {
        $data = AssessmentElementData::from([
            'id'                           => $this->assessmentElementId,
            'standard_id'                  => $this->standardId,
            'kode'                         => $this->kode,
            'deskripsi'                    => $this->deskripsi,
            'penjelasan_kelengkapan_bukti' => $this->penjelasanKelengkapanBukti,
            'proof_method_id'              => $this->proofMethodId ?: null,
            'urutan'                       => $this->urutan,
        ]);

        $action->execute($data);

        $this->emit('flash.success', 'Elemen Penilaian berhasil disimpan.');
        $this->emit('assessment-element-saved');
        $this->hideModal();
    }

    public function hideModal(): void
    {
        $this->isDeferred = true;
        $this->dispatchBrowserEvent('input-assessment-element.hide-modal');
    }

    public function render(): View
    {
        return view('livewire.pages.akreditasi.modal.input-assessment-element');
    }
}
