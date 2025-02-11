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
        $this->emit('select2.hydrate');
    }

    public function getTahunProperty(): int
    {
        return app(RKATSettings::class)->tahun;
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

    public function prepare(array $options): void
    {
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
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

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
            $this->dispatchBrowserEvent('data-saved');
            $this->emit('flash.info', 'Data Pemakaian RKAT baru sedang diproses!');
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

                $this->dispatchBrowserEvent('data-saved');
                $this->emit('flash.success', 'Data Pemakaian RKAT baru berhasil disimpan!');
            } catch (\Exception $e) {
                $this->dispatchBrowserEvent('data-failed');
                $this->emit('flash.success', 'Terjadi kegagalan pada saat menyimpan pemakaian RKAT!');
            }
        }
    }

    public function update(): void
    {
        if (! $this->isUpdating()) {
            $this->create();
        }

        if (user()->cannot('keuangan.rkat-pelaporan.update')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $this->validate();

        /** @var PemakaianAnggaran */
        $pemakaianAnggaran = PemakaianAnggaran::find($this->pemakaianAnggaranId);

        tracker_start('mysql_smc');

        $pemakaianAnggaran->update([
            'judul'              => $this->keterangan,
            'tgl_dipakai'        => $this->tglPakai,
            'anggaran_bidang_id' => $this->anggaranBidangId,
        ]);

        // hapus data yang ada terlebih dahulu, lalu lakukan insert ulang
        $pemakaianAnggaran
            ->detail()
            ->delete();

        $pemakaianAnggaran
            ->detail()
            ->createMany($this->detail);

        tracker_end('mysql_smc');

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', 'Data Pemakaian RKAT baru berhasil diupdate!');
    }

    public function delete(): void
    {
        if (user()->cannot('keuangan.rkat-pelaporan.delete')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        tracker_start('mysql_smc');

        PemakaianAnggaran::query()
            ->where('id', $this->pemakaianAnggaranId)
            ->delete();

        tracker_end('mysql_smc');

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', 'Data Pemakaian RKAT baru berhasil dihapus!');
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
