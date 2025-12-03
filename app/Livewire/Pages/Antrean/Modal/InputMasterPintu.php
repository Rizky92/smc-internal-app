<?php

namespace App\Livewire\Pages\Antrean\Modal;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Aplikasi\Pintu;
use App\Models\Aplikasi\SetPintuSmc;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class InputMasterPintu extends Component
{
    use DeferredModal;
    use Filterable;
    use FlashComponent;

    /** @var string */
    public $kodePintu;

    /** @var string */
    public $namaPintu;

    /** @var string|null Hold original values for lookup when updating/deleting */
    public $originalKodePintu;

    /** @var string|null Hold original values for lookup when updating/deleting */
    public $originalNamaPintu;

    /** @var mixed */
    protected $listeners = [
        'prepare',
        'pintu.hide-modal' => 'hideModal',
        'pintu.show-modal' => 'showModal',
    ];

    protected function rules(): array
    {
        $rules = collect([
            'kodePintu'      => ['required', 'string'],
            'namaPintu'      => ['required', 'string'],
        ]);

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

    public function render(): View
    {
        return view('livewire.pages.antrean.modal.input-master-pintu');
    }

    public function prepare(array $options): void
    {
        $this->kodePintu = $options['kodePintu'];
        $this->namaPintu = $options['namaPintu'];

        // Save original composite key values so subsequent edits to the form
        // won't break lookup when updating or deleting the record.
        $this->originalKodePintu = $options['kodePintu'] ?? null;
        $this->originalNamaPintu = $options['namaPintu'] ?? null;
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
            tracker_start('mysql_sik');

            DB::connection('mysql_sik')->transaction(function () {
                Pintu::create([
                    'kd_pintu' => $this->kodePintu,
                    'nm_pintu' => $this->namaPintu,
                ]);
            });

            tracker_end('mysql_sik');

            $this->dispatchBrowserEvent('data-saved');
            $this->emit('flash.success', 'Data Pintu baru berhasil disimpan!');
            $this->defaultValues();
        } catch (Exception $e) {
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

        // Use query builder update because this model does not define a single primary key.
        // Calling $model->update() would cause Eloquent to attempt an update using an empty
        // primary key column which produces SQL like "where `` is null".
        // Determine lookup key (original if present) and prevent editing when
        // there are existing mappings. This avoids accidental inconsistency.
        $lookupKodePintu = $this->originalKodePintu ?? $this->kodePintu;

        // If mappings exist for this pintu, disallow editing here and instruct
        // the user to remove mappings first (safer than implicit cascade).
        if (SetPintuSmc::where('kd_pintu', $lookupKodePintu)->exists()) {
            $this->dispatchBrowserEvent('data-denied');
            $this->emit('flash.error', 'Pintu ini sudah digunakan pada mapping. Hapus mapping di Manajemen Pintu terlebih dahulu untuk bisa mengubah data Pintu.');

            return;
        }

        try {
            tracker_start('mysql_sik');

            $updated = Pintu::query()
                ->where('kd_pintu', $lookupKodePintu)
                ->update([
                    'kd_pintu' => $this->kodePintu,
                    'nm_pintu' => $this->namaPintu,
                ]);

            tracker_end('mysql_sik');

            if ($updated === 0) {
                $this->dispatchBrowserEvent('data-not-found');
                $this->emit('flash.error', 'Tidak dapat menemukan data untuk diperbarui.');
            } else {
                $this->dispatchBrowserEvent('data-saved');
                $this->emit('flash.success', 'Data Pintu berhasil diperbarui!');
            }

            $this->defaultValues();
        } catch (Exception $e) {
            tracker_dispose('mysql_sik');
            $this->dispatchBrowserEvent('data-failed');
            $this->emit('flash.warning', 'Terjadi kegagalan pada saat memperbarui data Pintu!');
            $this->defaultValues();
        }
    }

    public function delete(): void
    {
        $pintu = Pintu::query()->where('kd_pintu', $this->kodePintu)->first();

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

        if (SetPintuSmc::where('kd_pintu', $this->kodePintu)->exists()) {
            $this->dispatchBrowserEvent('data-denied');
            $this->emit('flash.error', 'Pintu ini sudah digunakan pada mapping. Hapus mapping di Manajemen Pintu terlebih dahulu untuk bisa menghapus data Pintu.');

            return;
        }

        tracker_start('mysql_sik');

        $lookupPintu = $this->originalKodePintu ?? $this->kodePintu;

        $deleted = Pintu::query()
            ->where('kd_pintu', $lookupPintu)
            ->delete();

        tracker_end('mysql_sik');

        if ($deleted === 0) {
            $this->dispatchBrowserEvent('data-not-found');
            $this->emit('flash.error', 'Tidak dapat menemukan data yang bisa dihapus. Silahkan coba kembali.');
        } else {
            $this->dispatchBrowserEvent('data-success');
            $this->emit('flash.success', 'Data pintu berhasil dihapus!');
        }

        $this->defaultValues();
    }

    public function isUpdating(): bool
    {
        // Previously this returned true whenever kodePintu was set. That caused the
        // modal to switch to edit mode as soon as the user selected a Pintu from
        // the select box while creating a new mapping. Use the presence of the
        // original composite key (set by prepare()) to indicate edit mode.
        return $this->originalKodePintu !== null;
    }

    protected function defaultValues(): void
    {
        $this->kodePintu = '';
        $this->namaPintu = '';
        $this->originalKodePintu = null;
        $this->originalNamaPintu = null;
    }
}
