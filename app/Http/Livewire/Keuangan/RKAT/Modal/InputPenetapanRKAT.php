<?php

namespace App\Http\Livewire\Keuangan\RKAT\Modal;

use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Settings\RKATSettings;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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

    /** @var mixed */
    protected $listeners = [
        'prepare',
        'penetapan-rkat.show-modal' => 'showModal',
        'penetapan-rkat.hide-modal' => 'hideModal',
    ];

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

        /** @var \App\Models\Keuangan\RKAT\AnggaranBidang */
        $data = AnggaranBidang::find($id);

        $this->anggaranId = $data->anggaran_id;
        $this->bidangId = $data->bidang_id;
        $this->nominalAnggaran = $data->nominal_anggaran;
    }

    public function create(): void
    {
        if ($this->isUpdating()) {
            $this->update();

            return;
        }

        if (!Auth::user()->can('keuangan.rkat.penetapan-rkat.create')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $settings = app(RKATSettings::class);

        if (now()->between($settings->batas_input_awal, $settings->batas_input_akhir)) {
            $this->emit('flas.error', 'Batas waktu penetapan RKAT melewati periode');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $this->validate();

        AnggaranBidang::create([
            'anggaran_id'      => $this->anggaranId,
            'bidang_id'        => $this->bidangId,
            'tahun'            => $settings->tahun,
            'nominal_anggaran' => round($this->nominalAnggaran, 2),
        ]);

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', 'Data berhasil disimpan!');
    }

    public function update(): void
    {
        if (! $this->isUpdating()) {
            $this->create();

            return;
        }

        if (!Auth::user()->can('keuangan.rkat.penetapan-rkat.update')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $this->validate();

        AnggaranBidang::query()
            ->whereId($this->anggaranBidangId)
            ->update([
                'anggaran_id'      => $this->anggaranId,
                'bidang_id'        => $this->bidangId,
                'tahun'            => $this->tahun,
                'nominal_anggaran' => round($this->nominalAnggaran, 2),
            ]);

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', 'Data berhasil diupdate!');
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
