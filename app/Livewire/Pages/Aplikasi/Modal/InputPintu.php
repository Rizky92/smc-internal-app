<?php

namespace App\Livewire\Pages\Aplikasi\Modal;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Antrian\Jadwal;
use App\Models\Aplikasi\Pintu;
use App\Models\Aplikasi\SetPintuSmc;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class InputPintu extends Component
{
    use DeferredModal;
    use Filterable;
    use FlashComponent;

    /** @var string */
    public $kodePintu;

    /** @var array<string> */
    public $selectedJadwal;

    /** @var string */
    public $namaPintu;

    /** @var string */
    public $kodePoliklinik;

    /** @var string */
    public $kodeDokter;

    /** @var string|null */
    public $originalKodePintu;

    protected function rules(): array
    {
        $rules = collect([
            'kodePintu'      => ['required', 'string'],
            'kodePoliklinik' => ['required', 'string'],
            'kodeDokter'     => ['required', 'string'],
            'selectedJadwal' => ['required', 'array'],
        ]);

        return $rules->all();
    }

    /**
     * Handler for JS-emitted selected jadwal values (from select2).
     * Accepts an array of strings like ["KD_DOKTER|KD_POLI", ...] or a JSON string.
     *
     * Livewire 3 spreads an associative array dispatched from JS as PHP named
     * arguments (plain ...$array semantics, nothing Livewire-specific), so the
     * browser side wraps the payload as {data} to match this parameter name -
     * without that wrapping, a lone string still binds correctly (dispatch()
     * wraps a bare string as [string] before spreading), but an empty
     * selection sends [] and spreads to zero arguments, leaving $data
     * unresolvable.
     */
    #[On('inputPintu.setSelectedJadwal')]
    public function setSelectedJadwal($data): void
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (is_array($decoded)) {
                $data = $decoded;
            } else {
                // when select2 sends a comma separated string, normalize to array
                $data = $data === '' ? [] : explode(',', $data);
            }
        }

        $this->selectedJadwal = is_array($data) ? $data : [];

        // Optionally parse and populate kodeDokter/kodePoliklinik when single select
        if (count($this->selectedJadwal) === 1) {
            [$kd_dokter, $kd_poli] = array_pad(explode('|', $this->selectedJadwal[0]), 2, null);
            $this->kodeDokter = $kd_dokter;
            $this->kodePoliklinik = $kd_poli;
        }
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function hydrate(): void
    {
        $this->dispatch('select2.hydrate');
    }

    public function getJadwalPraktikProperty(): Collection
    {
        $pairs = Jadwal::query()->distinct()->get(['kd_dokter', 'kd_poli']);

        $pairs->loadMissing(['dokter', 'poliklinik']);

        return $pairs;
    }

    public function render(): View
    {
        return view('livewire.pages.aplikasi.modal.input-pintu');
    }

    /**
     * Fed by manajemen-pintu.blade.php's loadData(), which dispatches
     * {kodePintu, kodePoliklinik, kodeDokter} - an associative array from JS,
     * which Livewire 3 spreads as named PHP arguments. A single array
     * $options parameter here never matched any of those names and always
     * threw BindingResolutionException; kodePoliklinik/kodeDokter are
     * accepted but unused; nothing dispatches them today.
     */
    #[On('prepare')]
    public function prepare($kodePintu = null, $kodePoliklinik = null, $kodeDokter = null): void
    {
        // If no kd provided, treat as create
        if (empty($kodePintu)) {
            $this->defaultValues();

            return;
        }

        $this->originalKodePintu = $kodePintu;

        // Load pintu record to fill fields
        $pintu = Pintu::query()->where('kd_pintu', $kodePintu)->first();

        $this->kodePintu = $pintu->kd_pintu ?? $kodePintu;
        $this->namaPintu = $pintu->nm_pintu ?? '';

        // Load existing mappings and populate selectedJadwal as array of "kd_dokter|kd_poli"
        $mappings = SetPintuSmc::query()->where('kd_pintu', $kodePintu)->get(['kd_dokter', 'kd_poli']);

        $this->selectedJadwal = $mappings->map(fn ($m) => $m->kd_dokter.'|'.$m->kd_poli)->toArray();

        // If there is at least one mapping, prefill kodeDokter/kodePoliklinik with the first
        if (! empty($this->selectedJadwal)) {
            [$firstDokter, $firstPoli] = array_pad(explode('|', $this->selectedJadwal[0]), 2, null);
            $this->kodeDokter = $firstDokter;
            $this->kodePoliklinik = $firstPoli;
        }

        // Notify front-end to sync select2 value for selectedJadwal
        $this->dispatch('inputPintu.syncSelectedJadwal', $this->selectedJadwal);
    }

    public function update(): void
    {
        if (user()->cannot('antrean.manajemen-pintu.update')) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatch('data-denied');

            return;
        }

        $this->validate();

        try {
            tracker_start('mysql_sik');

            DB::connection('mysql_sik')->transaction(function () {
                // Update or rename pintu
                if ($this->originalKodePintu !== $this->kodePintu) {
                    // If kode changed, update the primary key value
                    Pintu::where('kd_pintu', $this->originalKodePintu)
                        ->update(['kd_pintu' => $this->kodePintu, 'nm_pintu' => $this->namaPintu]);
                } else {
                    Pintu::where('kd_pintu', $this->kodePintu)
                        ->update(['nm_pintu' => $this->namaPintu]);
                }

                // Replace mappings: delete old then create new
                SetPintuSmc::where('kd_pintu', $this->originalKodePintu)->delete();

                foreach ($this->selectedJadwal as $jadwal) {
                    [$kd_dokter, $kd_poli] = array_pad(explode('|', $jadwal), 2, null);

                    if (empty($kd_dokter) || empty($kd_poli)) {
                        throw new Exception("Nilai jadwal tidak valid: {$jadwal}");
                    }

                    SetPintuSmc::create([
                        'kd_pintu'  => $this->kodePintu,
                        'kd_dokter' => $kd_dokter,
                        'kd_poli'   => $kd_poli,
                    ]);
                }
            });

            tracker_end('mysql_sik');

            $this->dispatch('data-saved');
            $this->dispatch('flash.success', 'Data pintu berhasil diperbarui.');
            $this->defaultValues();
        } catch (Exception $e) {
            logger()->error('Gagal mengupdate Pintu: '.$e->getMessage(), ['exception' => $e, 'payload' => [
                'original'       => $this->originalKodePintu,
                'kodePintu'      => $this->kodePintu,
                'selectedJadwal' => $this->selectedJadwal,
            ]]);

            $this->dispatch('data-failed');
            $this->dispatch('flash.error', "Terjadi kegagalan saat memperbarui data pintu: {$e->getMessage()}");
            $this->defaultValues();
        }
    }

    public function create(): void
    {
        if (user()->cannot('antrean.manajemen-pintu.create')) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatch('data-denied');

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
                // Prevent duplicate primary key insertion
                if (Pintu::where('kd_pintu', $this->kodePintu)->exists()) {
                    throw new Exception("Kode pintu '{$this->kodePintu}' sudah ada.");
                }

                Pintu::create([
                    'kd_pintu' => $this->kodePintu,
                    'nm_pintu' => $this->namaPintu,
                ]);

                // ambil kd_pintu dan kd_dokter dari selectedJadwal
                foreach ($this->selectedJadwal as $jadwal) {
                    [$kd_dokter, $kd_poli] = array_pad(explode('|', $jadwal), 2, null);

                    if (empty($kd_dokter) || empty($kd_poli)) {
                        throw new Exception("Nilai jadwal tidak valid: {$jadwal}");
                    }

                    SetPintuSmc::create([
                        'kd_pintu'  => $this->kodePintu,
                        'kd_dokter' => $kd_dokter,
                        'kd_poli'   => $kd_poli,
                    ]);
                }
            });

            tracker_end('mysql_sik');

            $this->dispatch('data-saved');
            $this->dispatch('flash.success', 'Data pintu berhasil disimpan.');
            $this->defaultValues();
        } catch (Exception $e) {
            // Log full exception to storage/logs/laravel.log so we can inspect root cause
            logger()->error('Gagal menyimpan Pintu: '.$e->getMessage(), [
                'exception' => $e,
                'payload'   => [
                    'kodePintu'      => $this->kodePintu,
                    'selectedJadwal' => $this->selectedJadwal,
                    'namaPintu'      => $this->namaPintu,
                ],
            ]);

            $this->dispatch('data-failed');

            // Surface the specific error message to the user (useful in dev). In production you may keep a generic message.
            $this->dispatch('flash.error', "Terjadi kegagalan saat menyimpan data pintu: {$e->getMessage()}");
            $this->defaultValues();
        }
    }

    public function delete(): void
    {
        if (user()->cannot('antrean.manajemen-pintu.delete')) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatch('data-denied');

            return;
        }

        // Ensure we have a target to delete
        if (empty($this->originalKodePintu)) {
            $this->dispatch('flash.error', 'Tidak ada data yang dipilih untuk dihapus.');

            return;
        }

        try {
            tracker_start('mysql_sik');

            DB::connection('mysql_sik')->transaction(function () {
                // Delete mappings first
                SetPintuSmc::where('kd_pintu', $this->originalKodePintu)->delete();

                // Delete the pintu record
                Pintu::where('kd_pintu', $this->originalKodePintu)->delete();
            });

            tracker_end('mysql_sik');

            $this->dispatch('data-saved');
            $this->dispatch('flash.success', 'Data pintu berhasil dihapus.');
            $this->defaultValues();
        } catch (Exception $e) {
            logger()->error('Gagal menghapus Pintu: '.$e->getMessage(), [
                'exception' => $e,
                'payload'   => ['original' => $this->originalKodePintu],
            ]);

            $this->dispatch('data-failed');
            $this->dispatch('flash.error', "Terjadi kegagalan saat menghapus data pintu: {$e->getMessage()}");
            $this->defaultValues();
        }
    }

    public function isUpdating(): bool
    {
        return $this->originalKodePintu !== null;
    }

    protected function defaultValues(): void
    {
        $this->kodePintu = '';
        $this->namaPintu = '';
        $this->kodePoliklinik = '';
        $this->kodeDokter = '';
        $this->selectedJadwal = [];
        $this->originalKodePintu = null;
    }
}
