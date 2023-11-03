<?php

namespace App\Livewire\Pages\Keuangan\Modal;

use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Settings\RKATSettings;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Component;

class RKATInputPenetapan extends Component
{
    use FlashComponent, Filterable, DeferredModal;

    /** @var int */
    public $anggaranBidangId;

    /** @var int */
    public $anggaranId;

    /** @var int */
    public $bidangId;

    /** @var string */
    public $namaKegiatan;

    /** @var string */
    public $deskripsi;

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
            'namaKegiatan'    => ['required', 'string'],
            'deskripsi'       => ['string'],
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
        return Cache::remember('semua_bidang', now()->addMinutes(5), fn (): Collection =>
            Bidang::query()
                ->with('parent')
                ->hasParent()
                ->get()
                ->mapWithKeys(fn (Bidang $model) => [$model->id => sprintf('%s - %s', $model->parent->nama, $model->nama)])
        );
    }

    public function getTahunProperty(): int
    {
        return app(RKATSettings::class)->tahun;
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.modal.rkat-input-penetapan');
    }

    public function prepare(int $id = -1): void
    {
        $this->anggaranBidangId = $id;

        /** @var \App\Models\Keuangan\RKAT\AnggaranBidang */
        $data = AnggaranBidang::find($id);

        $this->anggaranId = $data->anggaran_id;
        $this->bidangId = $data->bidang_id;
        $this->nominalAnggaran = $data->nominal_anggaran;
        $this->namaKegiatan = $data->nama_kegiatan;
        $this->deskripsi = $data->deskripsi;
    }

    public function create(): void
    {
        if ($this->isUpdating()) {
            $this->update();

            return;
        }

        if (user()->cannot('keuangan.rkat-penetapan.create')) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $settings = app(RKATSettings::class);

        if (! now()->between($settings->tgl_penetapan_awal, $settings->tgl_penetapan_akhir)) {
            $this->flashError('Batas waktu penetapan RKAT melewati periode yang ditetapkan!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $this->validate();

        tracker_start();

        AnggaranBidang::create([
            'anggaran_id'      => $this->anggaranId,
            'bidang_id'        => $this->bidangId,
            'nama_kegiatan'    => $this->namaKegiatan,
            'deskripsi'        => $this->deskripsi,
            'tahun'            => $settings->tahun,
            'nominal_anggaran' => round($this->nominalAnggaran, 2),
        ]);

        tracker_end();

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', 'Data berhasil disimpan!');
    }

    public function update(): void
    {
        if (!$this->isUpdating()) {
            $this->create();

            return;
        }

        if (user()->cannot('keuangan.rkat-penetapan.update')) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $this->validate();

        AnggaranBidang::query()
            ->whereId($this->anggaranBidangId)
            ->update([
                'anggaran_id'      => $this->anggaranId,
                'bidang_id'        => $this->bidangId,
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
