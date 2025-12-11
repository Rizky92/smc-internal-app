<?php

namespace App\Livewire\Pages\Aplikasi\Modal;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Aplikasi\Pintu;
use App\Models\Aplikasi\SetPintuSmc;
use App\Models\Kepegawaian\Dokter;
use App\Models\Perawatan\Poliklinik;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class InputPintu extends Component
{
    use DeferredModal;
    use Filterable;
    use FlashComponent;

    public $pintuPlaceholder = '-1';

    public $pintuPlaceholderText = '-';

    /** @var string */
    public $kodePintu;

    /** @var string */
    public $kodePoliklinik;

    /** @var string */
    public $kodeDokter;

    /** @var string|null Hold original values for lookup when updating/deleting */
    public $originalKodePintu;

    /** @var string|null */
    public $originalKodePoliklinik;

    /** @var string|null */
    public $originalKodeDokter;

    /** @var mixed */
    protected $listeners = [
        'prepare',
        'pintu.hide-modal' => 'hideModal',
        'pintu.show-modal' => 'showModal',
        // listeners for JS emitted events from select2
        'inputPintu.setKodePintu'      => 'setKodePintu',
        'inputPintu.setKodePoliklinik' => 'setKodePoliklinik',
        'inputPintu.setKodeDokter'     => 'setKodeDokter',
    ];

    public function setKodePintu($value): void
    {
        $this->kodePintu = $value;
    }

    public function setKodePoliklinik($value): void
    {
        $this->kodePoliklinik = $value;
    }

    public function setKodeDokter($value): void
    {
        $this->kodeDokter = $value;
    }

    protected function rules(): array
    {
        $rules = collect([
            'kodePintu'      => ['required', 'string'],
            'kodePoliklinik' => ['required', 'string'],
            'kodeDokter'     => ['required', 'string'],
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

    public function getPintuProperty(): Collection
    {
        return Pintu::pluck('nm_pintu', 'kd_pintu');
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
        $this->kodePintu = $options['kodePintu'];
        $this->kodePoliklinik = $options['kodePoliklinik'];
        $this->kodeDokter = $options['kodeDokter'];

        // Save original composite key values so subsequent edits to the form
        // won't break lookup when updating or deleting the record.
        $this->originalKodePintu = $options['kodePintu'] ?? null;
        $this->originalKodePoliklinik = $options['kodePoliklinik'] ?? null;
        $this->originalKodeDokter = $options['kodeDokter'] ?? null;
    }

    public function create(): void
    {
        if ($this->isUpdating()) {
            $this->update();

            return;
        }

        if (user()->cannot('antrean.manajemen-pintu.create')) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $this->validate();

        try {
            tracker_start('mysql_sik');

            DB::connection('mysql_sik')->transaction(function () {
                SetPintuSmc::create([
                    'kd_pintu'  => $this->kodePintu,
                    'kd_poli'   => $this->kodePoliklinik,
                    'kd_dokter' => $this->kodeDokter,
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
        try {
            tracker_start('mysql_sik');

            // Use original values to find the existing record. If originals are
            // not set fall back to current values (defensive).
            $lookupPintu = $this->originalKodePintu ?? $this->kodePintu;
            $lookupPoli = $this->originalKodePoliklinik ?? $this->kodePoliklinik;
            $lookupDokter = $this->originalKodeDokter ?? $this->kodeDokter;

            $updated = SetPintuSmc::query()
                ->where('kd_pintu', $lookupPintu)
                ->where('kd_dokter', $lookupDokter)
                ->where('kd_poli', $lookupPoli)
                ->update([
                    'kd_pintu'  => $this->kodePintu,
                    'kd_poli'   => $this->kodePoliklinik,
                    'kd_dokter' => $this->kodeDokter,
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
        $pintu = SetPintuSmc::query()
            ->where('kd_pintu', $this->kodePintu)
            ->where('kd_dokter', $this->kodeDokter)
            ->where('kd_poli', $this->kodePoliklinik)
            ->first();

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

        tracker_start('mysql_sik');

        $lookupPintu = $this->originalKodePintu ?? $this->kodePintu;
        $lookupPoli = $this->originalKodePoliklinik ?? $this->kodePoliklinik;
        $lookupDokter = $this->originalKodeDokter ?? $this->kodeDokter;

        $deleted = SetPintuSmc::query()
            ->where('kd_pintu', $lookupPintu)
            ->where('kd_dokter', $lookupDokter)
            ->where('kd_poli', $lookupPoli)
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
        $this->kodePoliklinik = '';
        $this->kodeDokter = '';
        $this->originalKodePintu = null;
        $this->originalKodePoliklinik = null;
        $this->originalKodeDokter = null;
    }
}
