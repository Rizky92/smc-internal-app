<?php

namespace App\Livewire\Pages\Keuangan\Modal;

use App\Jobs\Keuangan\ImportTarifRalanJob;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use App\Notifications\Notification;
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
            $this->dispatch('flash.error', 'Anda tidak memiliki izin untuk mengimpor data tarif ralan.');
            $this->dispatch('data-denied');

            return;
        }

        if (! $this->fileImport) {
            $this->dispatch('flash.error', 'File import belum diunggah.');

            return;
        }

        ImportTarifRalanJob::dispatch([
            'fileImport' => $this->fileImport,
            'userId'     => user()->nik,
        ]);

        Notification::make()
            ->message('Import tarif rawat jalan sedang berjalan')
            ->info()
            ->send(user());

        $this->fileImport = null;
        $this->dispatch('data-saved');
        $this->dispatch('flash.info', 'Proses impor data tarif ralan telah dimulai, silahkan tunggu beberapa saat.');
    }

    protected function defaultValues(): void
    {
        $this->fileImport = null;
    }
}
