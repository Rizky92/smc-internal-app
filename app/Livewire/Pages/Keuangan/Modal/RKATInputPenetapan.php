<?php

namespace App\Livewire\Pages\Keuangan\Modal;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\Settings\RKATSettings;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\On;
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

    protected function rules(): array
    {
        $tersimpan = $this->sudahDipakai()
            ? AnggaranBidang::find($this->anggaranBidangId)
            : null;

        $rules = collect([
            'anggaranId' => array_filter([
                'required', 'exists:anggaran,id',
                $tersimpan ? $this->tidakBerubah($tersimpan->anggaran_id, 'Kategori Anggaran') : null,
            ]),
            'bidangId' => array_filter([
                'required', 'exists:bidang,id',
                $tersimpan ? $this->tidakBerubah($tersimpan->bidang_id, 'Bidang') : null,
            ]),
            'nominalAnggaran' => ['required', 'numeric', 'min:0'],
        ]);

        if ($this->isUpdating()) {
            $rules->prepend(['required'], 'anggaranBidangId');
        }

        return $rules->all();
    }

    /**
     * Once Pemakaian Anggaran is charged to a Penetapan RKAT, changing its
     * Kategori Anggaran or Bidang would move that spending along with it.
     */
    private function tidakBerubah(int $tersimpan, string $label): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($tersimpan, $label): void {
            if ((int) $value !== $tersimpan) {
                $fail(sprintf('%s tidak dapat diubah karena Penetapan RKAT ini sudah memiliki Pemakaian Anggaran.', $label));
            }
        };
    }

    public function sudahDipakai(): bool
    {
        return $this->isUpdating() && PemakaianAnggaran::query()
            ->where('anggaran_bidang_id', $this->anggaranBidangId)
            ->exists();
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
        return Bidang::query()
            ->with('descendantsAndSelf')
            ->isRoot()
            ->get()
            ->map
            ->descendantsAndSelf
            ->flatten()
            ->mapWithKeys(fn (Bidang $model) => [
                $model->id => str($model->nama)
                    ->padLeft(strlen($model->nama) + (intval($model->depth) * 8), html_entity_decode('&nbsp;'))
                    ->value(),
            ]);
    }

    public function getTahunProperty(): int
    {
        return app(RKATSettings::class)->tahun;
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.modal.rkat-input-penetapan');
    }

    #[On('prepare')]
    public function prepare(int $id = -1): void
    {
        $data = AnggaranBidang::find($id);

        // A stale id arrives whenever the table is left open while somebody else
        // deletes the row. Fall back to create mode rather than dereferencing
        // null.
        if ($data === null) {
            $this->defaultValues();

            return;
        }

        $this->anggaranBidangId = $id;
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

        if (user()->cannot('keuangan.rkat-penetapan.create')) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatch('data-denied');

            return;
        }

        $settings = app(RKATSettings::class);

        if ($this->diLuarPeriodePenetapan()) {
            $this->flashError('Waktu penetapan RKAT diluar periode yang sudah ditetapkan!');
            $this->dispatch('data-denied');

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

            $this->dispatch('data-saved');
            $this->dispatch('flash.success', 'Data berhasil disimpan!');
        } catch (Exception $e) {
            $this->flashError('Terjadi kesalahan saat menyimpan data!');
            $this->dispatch('data-denied');
        }
    }

    public function update(): void
    {
        if (! $this->isUpdating()) {
            $this->create();

            return;
        }

        if (user()->cannot('keuangan.rkat-penetapan.update')) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatch('data-denied');

            return;
        }

        if ($this->diLuarPeriodePenetapan()) {
            $this->flashError('Batas waktu penetapan RKAT melewati periode yang ditetapkan!');
            $this->dispatch('data-denied');

            return;
        }

        $this->validate();

        try {
            tracker_start('mysql_smc');

            AnggaranBidang::findOrFail($this->anggaranBidangId)
                ->update([
                    'anggaran_id'      => $this->anggaranId,
                    'bidang_id'        => $this->bidangId,
                    'nominal_anggaran' => round($this->nominalAnggaran, 2),
                ]);

            tracker_end('mysql_smc');

            $this->defaultValues();
            $this->dispatch('data-saved');
            $this->dispatch('flash.success', 'Data berhasil diubah!');
        } catch (Exception $e) {
            tracker_dispose('mysql_smc');

            $this->dispatch('data-errored');
            $this->flashError('Terjadi kesalahan pada saat mengubah data');
        }
    }

    public function delete(): void
    {
        // Refuse when nothing is selected, which is what the message says.
        // Written without the negation, this refused whenever a row *was*
        // selected and otherwise ran destroy() against the -1 placeholder, so
        // deleting a penetapan could never work.
        if (! $this->isUpdating()) {
            $this->flashError('Data tidak ditemukan!');
            $this->dispatch('data-denied');

            return;
        }

        if (user()->cannot('keuangan.rkat-penetapan.delete')) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatch('data-denied');

            return;
        }

        if ($this->diLuarPeriodePenetapan()) {
            $this->flashError('Batas waktu penetapan RKAT melewati periode yang ditetapkan!');
            $this->dispatch('data-denied');

            return;
        }

        $this->validate();

        // Deleting it would leave that spending charged to no budget.
        $jumlahPemakaian = PemakaianAnggaran::query()
            ->where('anggaran_bidang_id', $this->anggaranBidangId)
            ->count();

        if ($jumlahPemakaian > 0) {
            $this->flashError(sprintf(
                'Penetapan RKAT ini tidak dapat dihapus karena sudah memiliki %d Pemakaian Anggaran!',
                $jumlahPemakaian
            ));
            $this->dispatch('data-denied');

            return;
        }

        try {
            tracker_start('mysql_smc');

            AnggaranBidang::destroy($this->anggaranBidangId);

            tracker_end('mysql_smc');

            $this->defaultValues();
            $this->dispatch('data-deleted');
            $this->dispatch('flash.success', 'Data berhasil dihapus!');
        } catch (Exception $e) {
            tracker_dispose('mysql_smc');

            $this->defaultValues();
            $this->dispatch('data-errored');
            $this->dispatch('flash.error', 'Terjadi kesalahan pada saat menghapus data!');
        }
    }

    /**
     * No role is exempt here. Superadmin is still offered the actions outside
     * the period (RKATPenetapan::bisaTetapkanRKAT()), and is told on saving
     * that the period has passed.
     */
    private function diLuarPeriodePenetapan(): bool
    {
        $settings = app(RKATSettings::class);

        return ! now()->between($settings->tgl_penetapan_awal, $settings->tgl_penetapan_akhir);
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
        $this->nominalAnggaran = '';
    }
}
