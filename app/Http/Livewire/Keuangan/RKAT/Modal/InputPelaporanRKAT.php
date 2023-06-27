<?php

namespace App\Http\Livewire\Keuangan\RKAT\Modal;

use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\Settings\RKATSettings;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class InputPelaporanRKAT extends Component
{
    use Filterable, DeferredModal;

    /** @var int */
    public $pemakaianAnggaranId;

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

    protected function rules(): array
    {
        $rules = collect([
            'anggaranBidangId'    => ['required', 'exists:anggaran_bidang,id'],
            'tglPakai'            => ['required', 'date'],
            'nominalPemakaian'    => ['required', 'numeric'],
            'deskripsi'           => ['required', 'string'],
        ]);

        if ($this->isUpdating()) {
            $rules->prepend(['required'], 'pemakaianAnggaranId');
        }

        return $rules->all();
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function hydrate(): void
    {
        $this->emit('select2.hydrate');
    }

    public function getDataRKATPerBidangProperty(): Collection
    {
        return AnggaranBidang::query()
            ->with(['anggaran', 'bidang'])
            ->get()
            ->mapWithKeys(function (AnggaranBidang $ab): array {
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
        $this->pemakaianAnggaranId = $options['pemakaianAnggaranId'] ?? -1;
        $this->anggaranBidangId = $options['anggaranBidangId'];
        $this->tglPakai = $options['tglPakai'];
        $this->nominalPemakaian = $options['nominalPemakaian'];
        $this->deskripsi = $options['deskripsi'];
    }

    public function create(): void
    {
        if ($this->isUpdating()) {
            $this->update();

            return;
        }

        if (! Auth::user()->can('keuangan.rkat.pelaporan-rkat.input-laporan-rkat')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $settings = App(RKATSettings::class);

        if (now()->between($settings->batas_input_awal, $settings->batas_input_akhir)) {
            $this->emit('flash.error', 'Waktu input laporan penggunaan RKAT telah bahis!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $this->validate();

        PemakaianAnggaran::create([
            'deskripsi'          => $this->deskripsi,
            'nominal_pemakaian'  => $this->nominalPemakaian,
            'tgl_dipakai'        => $this->tglPakai,
            'anggaran_bidang_id' => $this->anggaranBidangId,
            'user_id'            => Auth::user()->nik,
        ]);

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', 'Data Pemakaian RKAT baru berhasil ditambahkan!');
    }

    public function update(): void
    {
        if (! $this->isUpdating()) {
            $this->create();
        }

        if (! Auth::user()->can('keuangan.rkat.pelaporan-rkat.edit-laporan-rkat')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $this->validate();

        PemakaianAnggaran::query()
            ->where('id', $this->pemakaianAnggaranId)
            ->update([
                'anggaran_bidang_id' => $this->anggaranBidangId,
                'tgl_pakai'          => $this->tglPakai,
                'nominal_pemakaian'  => $this->nominalPemakaian,
                'deskripsi'          => $this->deskripsi,
            ]);
        
        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', 'Data Pemakaian RKAT baru berhasil diupdate!');
    }

    protected function defaultValues(): void
    {
        $this->pemakaianAnggaranId = -1;
        $this->anggaranBidangId = -1;
        $this->tglPakai = '';
        $this->nominalPemakaian = 0;
        $this->deskripsi = '';
    }

    public function isUpdating(): bool
    {
        return $this->pemakaianAnggaranId !== -1;
    }
}
