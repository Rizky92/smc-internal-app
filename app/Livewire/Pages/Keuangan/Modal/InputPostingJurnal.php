<?php

namespace App\Livewire\Pages\Keuangan\Modal;

use App\Models\Keuangan\Rekening;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use Illuminate\View\View;
use Livewire\Component;

class InputPostingJurnal extends Component
{
    use FlashComponent, Filterable, DeferredModal;

    /** @var string */
    public $kodeRekening;

    /** @var mixed */
    protected $listeners = [
        'prepare',
        'posting-jurnal.hide-modal' => 'hideModal',
        'posting-jurnal.show-modal' => 'showModal',
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function hydrate(): void 
    {
        $this->emit('select2.hydrate');
    }

    public function getRekeningProperty(): array
    {
        return Rekening::query()
            ->orderBy('kd_rek')
            ->pluck('nm_rek', 'kd_rek')
            ->all();
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.modal.input-posting-jurnal');
    }

    public function prepare(array $options): void
    {

    }

    protected function defaultValues(): void
    {
        //
    }

    protected function dataPerSheet(): array
    {
        return [
            //
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            //
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            //
        ];
    }
}
