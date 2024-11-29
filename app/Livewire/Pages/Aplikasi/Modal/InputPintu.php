<?php

namespace App\Livewire\Pages\Aplikasi\Modal;

use App\Livewire\Concerns\DeferredModal;
use App\Models\Aplikasi\Pintu;
use App\Models\Perawatan\Poliklinik;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class InputPintu extends Component
{
    use DeferredModal;

    /** @var int */
    public $pintuId;

    /** @var string */
    public $kodePintu;

    /** @var string */
    public $namaPintu;

    /** @var array */
    public $kodePoliklinik;

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
    }

    public function create(): void
    {
        if (! user()->hasRole(config('permission.superadmin_name'))) {
            $this->dispatchBrowserEvent('data-denied');
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');

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
            });

            tracker_end();

            $this->dispatchBrowserEvent('data-saved');
            $this->emit('flash.success', 'Data Pintu baru berhasil disimpan!');
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('data-failed');
            $this->emit('flash.warning', 'Terjadi kegagalan pada saat menyimpan data Pintu!');
        }
    }

    public function update(): void
    {
        if (! user()->hasRole(config('permission.superadmin_name'))) {
            $this->dispatchBrowserEvent('data-denied');
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');

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

            $pintu->poliklinik()->sync($this->kodePoliklinik);
    
            tracker_end('mysql_smc');

            $this->dispatchBrowserEvent('data-saved');
            $this->emit('flash.success', 'Data Pintu berhasil diperbarui!');
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('data-failed');
            $this->emit('flash.warning', 'Terjadi kegagalan pada saat memperbarui data Pintu!');
        }
    }

    public function delete(): void
    {
        $pintu = Pintu::find($this->pintuId);

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

        tracker_start('mysql_smc');

        $pintu->delete();

        tracker_end('mysql_smc');

        $this->dispatchBrowserEvent('data-success');
        $this->emit('flash.success', 'Data pintu berhasil dihapus!');
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
    }
}
