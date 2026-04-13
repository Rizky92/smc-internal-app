<?php

namespace App\Livewire\Pages\Keuangan\Modal;

use App\Jobs\Keuangan\ImportTarifOperasiJob;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use App\Notifications\Notification;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ImportTarifOperasi extends Component
{
    use DeferredModal;
    use FlashComponent;
    use WithFileUploads;

    /** @var TemporaryUploadedFile|null */
    public $fileImport;

    /** @var mixed */
    protected $listeners = [
        'tarif-operasi.hide-modal' => 'hideModal',
        'tarif-operasi.show-modal' => 'showModal',
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.modal.import-tarif-operasi');
    }

    public function importData(): void
    {
        if (user()->cannot('keuangan.tarif-operasi.create')) {
            $this->emit('flash.error', 'Anda tidak memiliki izin untuk mengimpor data tarif operasi.');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        if (! $this->fileImport) {
            $this->emit('flash.error', 'File import belum diunggah.');

            return;
        }

        ImportTarifOperasiJob::dispatch([
            'fileImport' => $this->fileImport,
            'userId'     => user()->nik,
        ]);

        Notification::make()
            ->message('Import tarif operasi sedang berjalan')
            ->info()
            ->send(user());

        $this->fileImport = null;
        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.info', 'Proses impor data tarif operasi telah dimulai, silahkan tunggu beberapa saat.');
    }

    protected function defaultValues(): void
    {
        $this->fileImport = null;
    }
}
