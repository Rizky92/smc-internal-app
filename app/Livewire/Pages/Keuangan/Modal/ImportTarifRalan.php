<?php

namespace App\Livewire\Pages\Keuangan\Modal;

use App\Jobs\Keuangan\ImportTarifRalanJob;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ImportTarifRalan extends Component
{
    use DeferredModal;
    use FlashComponent;
    use WithFileUploads;

    /** @var TemporaryUploadedFile|null */
    public $fileImport;

    /** @var mixed */
    protected $listeners = [
        'tarif-ralan.hide-modal' => 'hideModal',
        'tarif-ralan.show-modal' => 'showModal',
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.modal.import-tarif-ralan');
    }

    public function importData(): void
    {
        if (user()->cannot('keuangan.tarif-ralan.create')) {
            $this->emit('flash.error', 'Anda tidak memiliki izin untuk mengimpor data tarif ralan.');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        if (! $this->fileImport) {
            $this->emit('flash.error', 'File import belum diunggah.');

            return;
        }

        ImportTarifRalanJob::dispatch([
            'fileImport' => $this->fileImport,
            'userId'     => user()->nik,
        ]);

        $this->fileImport = null;
        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.info', 'Proses impor data tarif ralan telah dimulai, silahkan tunggu beberapa saat.');
    }

    protected function defaultValues(): void
    {
        $this->fileImport = null;
    }
}
