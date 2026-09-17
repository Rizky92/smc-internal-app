<?php

namespace App\Livewire\Pages\Keuangan\Modal;

use App\Jobs\Keuangan\ImportPemakaianAnggaranDetail;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\Models\Keuangan\RKAT\PemakaianAnggaranDetail;
use App\Settings\RKATSettings;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class RKATInputPelaporan extends Component
{
    use DeferredModal;
    use Filterable;
    use FlashComponent;
    use WithFileUploads;

    /** @var int */
    public $pemakaianAnggaranId;

    /** @var int */
    public $anggaranBidangId;

    /** @var Carbon|\DateTime|string */
    public $tglPakai;

    /** @var string */
    public $keterangan;

    /** @var array<array-key, array{keterangan: string, nominal: numeric}> */
    public $detail;

    /** @var TemporaryUploadedFile|null */
    public $fileImport;

    protected function rules(): array
    {
        $rules = collect([
            'anggaranBidangId'    => ['required', 'exists:anggaran_bidang,id'],
            'tglPakai'            => ['required', 'date', $this->diTahunPenetapan()],
            'keterangan'          => ['required', 'string'],
            'detail'              => ['array'],
            'detail.*.keterangan' => ['nullable', 'string'],
            'detail.*.nominal'    => ['required', 'numeric', 'min:0'],
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
        $this->dispatch('select2.hydrate');
    }

    /**
     * A Pemakaian Anggaran is dated within the year of its Penetapan RKAT.
     */
    private function diTahunPenetapan(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $tahunPenetapan = AnggaranBidang::query()
                ->whereKey($this->anggaranBidangId)
                ->value('tahun');

            // No Penetapan chosen is anggaranBidangId's own error to report.
            if ($tahunPenetapan === null || $this->tahunPakai() === null) {
                return;
            }

            if ($this->tahunPakai() !== (int) $tahunPenetapan) {
                $fail(sprintf('Tgl. pemakaian harus berada di tahun Penetapan RKAT yang dipilih (%d).', $tahunPenetapan));
            }
        };
    }

    private function tahunPakai(): ?int
    {
        // carbon() reads a blank value as now.
        if (blank($this->tglPakai)) {
            return null;
        }

        try {
            return carbon($this->tglPakai)->year;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * The year of the tanggal pakai, which decides the Penetapan RKAT on offer.
     * The Tahun RKAT only stands in while the date cannot be read.
     */
    public function getTahunProperty(): int
    {
        return $this->tahunPakai() ?? app(RKATSettings::class)->tahun;
    }

    public function updatedTglPakai(): void
    {
        $masihDitawarkan = AnggaranBidang::query()
            ->whereKey($this->anggaranBidangId)
            ->where('tahun', $this->tahun)
            ->exists();

        if (! $masihDitawarkan) {
            $this->anggaranBidangId = -1;
        }
    }

    public function getDataRKATPerBidangProperty(): Collection
    {
        return AnggaranBidang::query()
            ->with(['anggaran', 'bidang'])
            ->where('tahun', $this->tahun)
            ->get()
            ->mapWithKeys(function (AnggaranBidang $ab): array {
                $namaAnggaran = $ab->anggaran->nama;
                $namaBidang = $ab->bidang->nama;
                $tahun = $ab->tahun;

                $string = collect([$namaBidang, $tahun, $namaAnggaran])
                    ->joinStr(' - ')
                    ->value();

                return [$ab->id => $string];
            });
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.modal.rkat-input-pelaporan');
    }

    #[On('prepare')]
    public function prepare(array $options = []): void
    {
        // The "Laporan Baru" trigger dispatches prepare with no options at all
        // (see rkat-pelaporan.blade.php's shown.bs.modal handler) to reset the
        // form for a new report; reading tglPakai/keterangan out of an empty
        // array below would leave them null instead of defaultValues()'s actual
        // defaults, and detail would end up [] instead of one blank row.
        if (empty($options)) {
            $this->defaultValues();

            return;
        }

        $this->anggaranBidangId = $options['anggaranBidangId'] ?? -1;
        $this->pemakaianAnggaranId = $options['pemakaianAnggaranId'] ?? -1;
        $this->tglPakai = $options['tglPakai'];
        $this->keterangan = $options['keterangan'];

        $detail = PemakaianAnggaranDetail::query()
            ->where('pemakaian_anggaran_id', $this->pemakaianAnggaranId)
            ->get();

        $this->detail = $detail->isEmpty() ? [] : $detail
            ->map(fn (PemakaianAnggaranDetail $model): array => [
                'keterangan' => $model->keterangan,
                'nominal'    => round($model->nominal),
            ])
            ->all();
    }

    public function create(): void
    {
        if ($this->isUpdating()) {
            $this->update();

            return;
        }

        if (user()->cannot('keuangan.rkat-pelaporan.create')) {
            $this->dispatch('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatch('data-denied');

            return;
        }

        $this->validate();

        if ($this->fileImport) {
            ImportPemakaianAnggaranDetail::dispatch([
                'keterangan'       => $this->keterangan,
                'tglPakai'         => $this->tglPakai,
                'anggaranBidangId' => $this->anggaranBidangId,
                'fileImport'       => $this->fileImport,
                'detail'           => $this->detail,
                'userId'           => user()->nik,
            ]);

            $this->fileImport = null;
            $this->dispatch('data-saved');
            $this->dispatch('flash.info', 'Data Pemakaian RKAT baru sedang diproses!');
        } else {
            try {
                tracker_start();

                DB::connection('mysql_smc')->transaction(function () {
                    $pemakaianAnggaran = PemakaianAnggaran::create([
                        'judul'              => $this->keterangan,
                        'tgl_dipakai'        => $this->tglPakai,
                        'anggaran_bidang_id' => $this->anggaranBidangId,
                        'user_id'            => user()->nik,
                    ]);

                    $pemakaianAnggaran->detail()->createMany($this->detail);
                });

                tracker_end();

                $this->dispatch('data-saved');
                $this->dispatch('flash.success', 'Data Pemakaian RKAT baru berhasil disimpan!');
            } catch (\Exception $e) {
                tracker_dispose('mysql_smc');

                $this->dispatch('data-failed');
                $this->dispatch('flash.error', 'Terjadi kegagalan pada saat menyimpan pemakaian RKAT!');
            }
        }
    }

    public function update(): void
    {
        if (! $this->isUpdating()) {
            $this->create();

            // Without this the method carried on into the update path, where
            // PemakaianAnggaran::find(-1) is null and ->update() on it is fatal.
            // RKATInputPenetapan::update() returns here for the same reason.
            return;
        }

        if (user()->cannot('keuangan.rkat-pelaporan.update')) {
            $this->dispatch('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatch('data-denied');

            return;
        }

        $this->validate();

        /** @var PemakaianAnggaran */
        $pemakaianAnggaran = PemakaianAnggaran::find($this->pemakaianAnggaranId);

        try {
            tracker_start('mysql_smc');

            // The lines are removed and written again, so the whole update has
            // to succeed or fail together: a failed write must not leave the
            // Pemakaian with no lines at all.
            DB::connection('mysql_smc')->transaction(function () use ($pemakaianAnggaran) {
                $pemakaianAnggaran->update([
                    'judul'              => $this->keterangan,
                    'tgl_dipakai'        => $this->tglPakai,
                    'anggaran_bidang_id' => $this->anggaranBidangId,
                ]);

                $pemakaianAnggaran
                    ->detail()
                    ->delete();

                $pemakaianAnggaran
                    ->detail()
                    ->createMany($this->detail);
            });

            tracker_end('mysql_smc');
        } catch (\Exception $e) {
            tracker_dispose('mysql_smc');

            $this->dispatch('data-failed');
            $this->dispatch('flash.error', 'Terjadi kegagalan pada saat mengubah pemakaian RKAT!');

            return;
        }

        $this->dispatch('data-saved');
        $this->dispatch('flash.success', 'Data Pemakaian RKAT baru berhasil diupdate!');
    }

    public function delete(): void
    {
        if (user()->cannot('keuangan.rkat-pelaporan.delete')) {
            $this->dispatch('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatch('data-denied');

            return;
        }

        tracker_start('mysql_smc');

        PemakaianAnggaran::query()
            ->where('id', $this->pemakaianAnggaranId)
            ->delete();

        tracker_end('mysql_smc');

        $this->dispatch('data-saved');
        $this->dispatch('flash.success', 'Data Pemakaian RKAT baru berhasil dihapus!');
    }

    public function addDetail(): void
    {
        $this->detail[] = [
            'keterangan' => '',
            'nominal'    => 0,
        ];
    }

    public function removeDetail(int $index): void
    {
        unset($this->detail[$index]);
    }

    public function isUpdating(): bool
    {
        return $this->pemakaianAnggaranId !== -1;
    }

    protected function defaultValues(): void
    {
        $this->pemakaianAnggaranId = -1;
        $this->anggaranBidangId = -1;
        $this->tglPakai = now()->toDateString();
        $this->keterangan = '';
        $this->detail = [[
            'keterangan' => '',
            'nominal'    => 0,
        ]];
    }
}
