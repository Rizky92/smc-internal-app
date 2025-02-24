<?php

namespace App\Livewire\Pages\Aplikasi\Modal;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Aplikasi\Pintu;
use App\Models\Kepegawaian\Dokter;
use App\Models\Perawatan\Poliklinik;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class InputPintu extends Component
{
    use DeferredModal;
    use FlashComponent;

    /** @var int */
    public $pintuId;

    /** @var string */
    public $kodePintu;

    /** @var string */
    public $namaPintu;

    /** @var array */
    public $kodePoliklinik;

    /** @var array */
    public $kodeDokter;

    /** @var mixed */
    protected $listeners = [
        'prepare',
        'aplikasi.pintu.hide-modal' => 'hideModal',
        'aplikasi.pintu.show-modal' => 'showModal',
    ];

    protected function rules(): array
    {
        $rules = collect([
            'kodePintu' => ['required', 'string'],
            'namaPintu' => ['required', 'string'],
        ]);

        if ($this->isUpdating()) {
            $rules->put('pintuId', ['required']);
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

    public function getPoliklinikProperty(): Collection
    {
        return Poliklinik::where('status', '1')->pluck('nm_poli', 'kd_poli');
    }

    public function getDokterProperty(): Collection
    {
        return Dokter::where('status', '1')->pluck('nm_dokter', 'kd_dokter');
    }

    public function render(): View
    {
        return view('livewire.pages.aplikasi.modal.input-pintu');
    }

    public function prepare(array $options): void
    {
        $this->pintuId = $options['pintuId'] ?? -1;
        $this->kodePintu = $options['kodePintu'];
        $this->namaPintu = $options['namaPintu'];

        if ($this->isUpdating()) {
            $pintu = Pintu::with('poliklinik')->find($this->pintuId);
            if ($pintu) {
                $this->kodePoliklinik = $pintu->poliklinik->pluck('kd_poli')->toArray();
            } else {
                $this->kodePoliklinik = [];
            }
        } else {
            $this->kodePoliklinik = [];
        }

        if ($this->isUpdating()) {
            $pintu = Pintu::with('dokter')->find($this->pintuId);
            if ($pintu) {
                $this->kodeDokter = $pintu->dokter->pluck('kd_dokter')->toArray();
            } else {
                $this->kodeDokter = [];
            }
        } else {
            $this->kodeDokter = [];
        }
    }

    public function create(): void
    {
        if (user()->cannot('antrean.manajemen-pintu.create')) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        if ($this->isUpdating()) {
            $this->update();

            return;
        }

        $this->validate();

        try {
            tracker_start();

            DB::connection('mysql_smc')->transaction(function () {
                $pintu = Pintu::create([
                    'kd_pintu' => $this->kodePintu,
                    'nm_pintu' => $this->namaPintu,
                ]);

                $pintu->poliklinik()->sync($this->kodePoliklinik);
                $pintu->dokter()->sync($this->kodeDokter);
            });

            tracker_end();

            $this->dispatchBrowserEvent('data-saved');
            $this->emit('flash.success', 'Data Pintu baru berhasil disimpan!');
            $this->defaultValues();
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('data-failed');
            $this->emit('flash.warning', 'Terjadi kegagalan pada saat menyimpan data Pintu!');
            $this->defaultValues();
        }
    }

    public function update(): void
    {
        if (user()->cannot('antrean.manajemen-pintu.update')) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        if (! $this->isUpdating()) {
            $this->create();
        }

        $this->validate();

        /** @var Pintu */
        $pintu = Pintu::find($this->pintuId);

        try {
            tracker_start('mysql_smc');

            $pintu->update([
                'kd_pintu' => $this->kodePintu,
                'nm_pintu' => $this->namaPintu,
            ]);

            $pintu->poliklinik()->detach();
            $pintu->dokter()->detach();

            $pintu->poliklinik()->sync($this->kodePoliklinik);
            $pintu->dokter()->sync($this->kodeDokter);

            tracker_end('mysql_smc');

            $this->dispatchBrowserEvent('data-saved');
            $this->emit('flash.success', 'Data Pintu berhasil diperbarui!');
            $this->defaultValues();
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('data-failed');
            $this->emit('flash.warning', 'Terjadi kegagalan pada saat memperbarui data Pintu!');
            $this->defaultValues();
        }
    }

    public function delete(): void
    {
        $pintu = Pintu::find($this->pintuId);

        if (user()->cannot('antrean.manajemen-pintu.delete')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        if (! $pintu) {
            $this->dispatchBrowserEvent('data-not-found');
            $this->emit('flash.error', 'Tidak dapat menemukan data yang bisa dihapus. Silahkan coba kembali.');

            return;
        }

        if ($pintu->poliklinik()->count() > 0) {
            $this->dispatchBrowserEvent('data-denied');
            $this->emit('flash.error', 'Pintu terkait masih ada poliklinik! Tidak boleh dihapus!');

            return;
        }

        if ($pintu->dokter()->count() > 0) {
            $this->dispatchBrowserEvent('data-denied');
            $this->emit('flash.error', 'Pintu terkait masih ada dokter! Tidak boleh dihapus!');

            return;
        }

        tracker_start('mysql_smc');

        $pintu->delete();

        tracker_end('mysql_smc');

        $this->dispatchBrowserEvent('data-success');
        $this->emit('flash.success', 'Data pintu berhasil dihapus!');
        $this->defaultValues();
    }

    public function isUpdating(): bool
    {
        return $this->pintuId !== -1;
    }

    public function defaultValues(): void
    {
        $this->pintuId = -1;
        $this->kodePintu = '';
        $this->namaPintu = '';
        $this->kodePoliklinik = [];
        $this->kodeDokter = [];
    }
}
