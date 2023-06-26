<?php

namespace App\Http\Livewire\Keuangan\RKAT\Modal;

use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class InputPenetapanRKAT extends Component
{
    use Filterable, DeferredModal;

    /** @var int */
    public $anggaranBidangId;

    /** @var int */
    public $anggaranId;

    /** @var int */
    public $bidangId;

    /** @var int|float */
    public $nominalAnggaran;

    protected function rules(): array
    {
        $rules = collect([
            'anggaranId'      => ['required', 'exists:anggaran,id'],
            'bidangId'        => ['required', 'exists:bidang,id'],
            'nominalAnggaran' => ['required', 'numeric'],
        ]);

        if ($this->isUpdating()) {
            $rules->prepend(['required'], 'anggaranBidangId');
        }

        return $rules->all();
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getKategoriAnggaranProperty(): Collection
    {
        return Anggaran::pluck('nama', 'id');
    }

    public function getBidangUnitProperty(): Collection
    {
        return Bidang::pluck('nama', 'id');
    }

    public function render(): View
    {
        return view('livewire.keuangan.rkat.modal.input-penetapan-rkat');
    }

    public function prepare(int $id = -1): void
    {
        $this->anggaranBidangId = $id;

        $data = AnggaranBidang::find($id);

        if (!$data) {
            $this->emit('flash.error', 'Tidak dapat menemukan data yang dipilih!');
            $this->emit('modal.unloaded');

            return;
        }

        $this->anggaranId = $data->anggaran_id;
        $this->bidangId = $data->bidang_id;
        $this->nominalAnggaran = $data->nominal_anggaran;
    }

    public function isUpdating(): bool
    {
        return $this->anggaranBidangId !== -1;
    }

    protected function defaultValues(): void
    {
        $this->anggaranBidangId = -1;
        $this->anggaranId = -1;
        $this->bidangId = -1;
        $this->nominalAnggaran = 0;
    }
}
