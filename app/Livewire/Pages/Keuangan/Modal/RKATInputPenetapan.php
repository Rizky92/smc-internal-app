<?php

namespace App\Livewire\Pages\Keuangan\Modal;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Settings\RKATSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class RKATInputPenetapan extends Component
{
    use DeferredModal;
    use Filterable;
    use FlashComponent;

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

        /** @var AnggaranBidang */
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

        if (!Auth::user()->can('keuangan.rkat-penetapan.create')) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $settings = app(RKATSettings::class);

        if (now()->between($settings->tgl_penetapan_awal, $settings->tgl_penetapan_akhir)) {
            $this->flashError('Batas waktu penetapan RKAT melewati periode yang ditetapkan!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $this->validate();

        try {
            tracker_start('mysql_smc');
    
            AnggaranBidang::create([
                'anggaran_id'      => $this->anggaranId,
                'bidang_id'        => $this->bidangId,
                'tahun'            => $settings->tahun,
                'nominal_anggaran' => round($this->nominalAnggaran, 2),
            ]);
    
            tracker_end('mysql_smc');
    
            $this->dispatchBrowserEvent('data-saved');
            $this->emit('flash.success', 'Data berhasil disimpan!');
        } catch (\Exception $e) {
            $this->flashError('Terjadi kesalahan saat menyimpan data!');
            $this->dispatchBrowserEvent('data-denied');
        }
    }

    public function delete(): void
    {
        if (user()->cannot('keuangan.rkat-penetapan.delete')) {
            $this->defaultValues();
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');
        }

        if (! $this->isUpdating()) {
            $this->flashError('Tidak dapat menemukan penetapan anggaran!');
            $this->dispatchBrowserEvent('data-not-found');

            return;
        }

        tracker_start('mysql_smc');

        try {
            $deleteBidang = AnggaranBidang::find($this->anggaranBidangId)
                ->delete();

            tracker_end('mysql_smc');

            $this->defaultValues();
            $this->dispatchBrowserEvent('data-deleted');
            $this->emit('flash.success', 'Data berhasil dihapus!');
        } catch (\Exception $e) {
            tracker_dispose('mysql_smc');

            if ($e->getCode() == 23000) {
                $this->emit('flash.info', 'Anggaran sudah digunakan, tidak dapat dihapus!');
            } else {
                $this->emit('flash.error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
            }
        }
    }
    
    public function update(): void
    {
        if (!$this->isUpdating()) {
            $this->create();

            return;
        }

        if (!Auth::user()->can('keuangan.rkat-penetapan.update')) {
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
