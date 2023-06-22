<?php

namespace App\Http\Livewire\Keuangan\RKAT\Modal;

use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class InputPelaporanRKAT extends Component
{
    use FlashComponent, Filterable, DeferredModal;

    /** @var bool */
    public $status;

    /** @var int */
    public $anggaranBidangId;

    /** @var \Carbon\Carbon|\DateTime|string */
    public $tglPakai;

    /** @var int|float */
    public $nominalPemakaian;

    /** @var string */
    public $deskripsi;

    /** @var mixed */
    protected $listeners = [
        'prepare',
        'pelaporan-rkat.hide-modal' => 'hideModal',
        'pelaporan-rkat.show-modal' => 'showModal',
    ];

    /** @var mixed */
    protected $rules = [
        'anggaranBidangId' => ['required', 'exists:anggaran_bidang'],
        'tglPakai'         => ['required', 'date'],
        'nominalPemakaian' => ['required', 'numeric'],
        'deskripsi'        => ['required', 'string'],
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataRKATPerBidangProperty(): Collection
    {
        return AnggaranBidang::query()
            ->with(['anggaran', 'bidang'])
            ->get()
            ->mapWithKeys(function (AnggaranBidang $ab) {
                $namaAnggaran = $ab->anggaran->nama;
                $namaBidang = $ab->bidang->nama;
                $tahun = $ab->tahun;

                $string = collect([$namaAnggaran, $namaBidang, $tahun])
                    ->joinStr(' - ')
                    ->value();

                return [$ab->id => $string];
            });
    }

    public function render(): View
    {
        return view('livewire.keuangan.rkat.modal.input-pelaporan-rkat');
    }

    public function prepare(array $options): void
    {
        $this->anggaranBidangId = $options['anggaranBidangId'];
        $this->tglPakai = $options['tglPakai'];
        $this->nominalPemakaian = $options['nominalPemakaian'];
        $this->deskripsi = $options['deskripsi'];
    }

    public function create(): void
    {
        if ($this->isUpdating) {
            $this->update();

            return;
        }

        if (! Auth::user()->can('keuangan.rkat.pelaporan-rkat.input-rkat')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $this->validate();

        PemakaianAnggaran::create([
            'deskripsi'          => $this->deskripsi,
            'nominal_pemakaian'  => $this->nominalPemakaian,
            'tgl_dipakai'        => $this->tglDipakai,
            'anggaran_bidang_id' => $this->anggaranBidangId,
            'user_id'            => Auth::user()->nik,
        ]);
    }

    public function update()
    {

    }

    public function reorder(int $id, int $position)
    {

    }

    protected function defaultValues(): void
    {
        $this->status = false;

        $this->anggaranBidangId = -1;
        $this->tglPakai = '';
        $this->nominalPemakaian = -1;
        $this->deskripsi = '';
    }

    public function isUpdating(): bool
    {
        return $this->status;
    }

    public function updating(): void
    {
        $this->status = true;
    }

    public function creating(): void
    {
        $this->status = false;
    }
}
