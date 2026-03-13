<?php

namespace App\Livewire\Pages\Keuangan\Modal;

use App\Jobs\Keuangan\ImportTarifRadiologiJob;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use App\Notifications\Notification;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ImportTarifRadiologi extends Component
{
    use DeferredModal;
    use FlashComponent;
    use WithFileUploads;

    /** @var TemporaryUploadedFile|null */
    public $fileImport;

    /** @var mixed */
    protected $listeners = [
        'tarif-radiologi.hide-modal' => 'hideModal',
        'tarif-radiologi.show-modal' => 'showModal',
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.modal.import-tarif-radiologi');
    }

    public function importData(): void
    {
        if (user()->cannot('keuangan.tarif-radiologi.create')) {
            $this->emit('flash.error', 'Anda tidak memiliki izin untuk mengimpor data tarif radiologi.');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        if (! $this->fileImport) {
            $this->emit('flash.error', 'File import belum diunggah.');

            return;
        }

        ImportTarifRadiologiJob::dispatch([
            'fileImport' => $this->fileImport,
            'userId'     => user()->nik,
        ]);

        Notification::make()
            ->message('Import tarif radiologi sedang berjalan')
            ->info()
            ->send(user());

        $this->fileImport = null;
        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.info', 'Proses impor data tarif radiologi telah dimulai, silahkan tunggu beberapa saat.');
    }

    protected function defaultValues(): void
    {
        $this->fileImport = null;
    }
}
