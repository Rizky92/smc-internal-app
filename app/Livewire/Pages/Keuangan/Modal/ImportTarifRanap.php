<?php

namespace App\Livewire\Pages\Keuangan\Modal;

use App\Jobs\Keuangan\ImportTarifRanapJob;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportTarifRanap extends Component
{
    use DeferredModal;
    use FlashComponent;
    use WithFileUploads;

    /** @var TemporaryUploadedFile|null */
    public $fileImport;

    /** @var mixed */
    protected $listeners = [
        'tarif-ranap.hide-modal' => 'hideModal',
        'tarif-ranap.show-modal' => 'showModal',
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.modal.import-tarif-ranap');
    }

    public function importData(): void
    {
        if (user()->cannot('keuangan.tarif-ranap.create')) {
            $this->emit('flash.error', 'Anda tidak memiliki izin untuk mengimpor data tarif ranap.');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        if (! $this->fileImport) {
            $this->emit('flash.error', 'File import belum diunggah.');

            return;
        }

        ImportTarifRanapJob::dispatch([
            'fileImport' => $this->fileImport,
            'userId'     => user()->nik,
        ]);

        $this->fileImport = null;
        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.info', 'Proses impor data tarif ranap telah dimulai, silahkan tunggu beberapa saat.');
    }

    protected function defaultValues(): void
    {
        $this->fileImport = null;
    }
}
