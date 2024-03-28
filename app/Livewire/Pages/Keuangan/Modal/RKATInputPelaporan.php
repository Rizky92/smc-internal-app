<?php

namespace App\Livewire\Pages\Keuangan\Modal;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\Models\Keuangan\RKAT\PemakaianAnggaranDetail;
use App\Settings\RKATSettings;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class RKATInputPelaporan extends Component
{
    use DeferredModal;
    use Filterable;
    use FlashComponent;

    /** @var int */
    public $pemakaianAnggaranId;

    /** @var int */
    public $anggaranBidangId;

    /** @var Carbon|\DateTime|string */
    public $tglPakai;

    /** @var string */
    public $keterangan;

    /** @var array */
    public $detail;

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
            'detail.*.nominal'    => ['required', 'numeric'],
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
        $this->validasiNominalPemakaian();

        tracker_start();

        $pemakaianAnggaran = PemakaianAnggaran::create([
            'judul'              => $this->keterangan,
            'tgl_dipakai'        => $this->tglPakai,
            'anggaran_bidang_id' => $this->anggaranBidangId,
            'user_id'            => user()->nik,
        ]);

        $pemakaianAnggaran
            ->detail()
            ->createMany($this->detail);

        tracker_end();

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', 'Data Pemakaian RKAT baru berhasil ditambahkan!');
    }

    public function update(): void
    {
        if (!$this->isUpdating()) {
            $this->create();
        }

        if (user()->cannot('keuangan.rkat-pelaporan.update')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $this->validate();
        $this->validasiNominalPemakaian();

        /** @var PemakaianAnggaran */
        $pemakaianAnggaran = PemakaianAnggaran::find($this->pemakaianAnggaranId);

        tracker_start();

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

        tracker_end();

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

        tracker_start();

        PemakaianAnggaran::query()
            ->where('id', $this->pemakaianAnggaranId)
            ->delete();

        tracker_end();

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
        $this->tglPakai = now()->format('Y-m-d');
        $this->keterangan = '';
        $this->detail = [[
            'keterangan' => '',
            'nominal'    => 0,
        ]];
    }

    private function validasiNominalPemakaian(): void
    {
        $nominalAnggaran = round(AnggaranBidang::whereId($this->anggaranBidangId)->value('nominal_anggaran'), 2);

        $anggaranDigunakan = round(PemakaianAnggaran::query()
            ->whereAnggaranBidangId($this->anggaranBidangId)
            ->when($this->pemakaianAnggaranId !== -1, fn (Builder $q): Builder => $q->whereId($this->pemakaianAnggaranId))
            ->withSum('detail as total_pemakaian', 'nominal')
            ->withCasts(['total_pemakaian' => 'float'])
            ->value('total_pemakaian'), 2);

        $pemakaianBaru = round(floatval(collect($this->detail)->sum('nominal')), 2);

        if ($pemakaianBaru > ($nominalAnggaran - $anggaranDigunakan)) {
            throw ValidationException::withMessages([
                'nominalPemakaian' => 'Pemakaian anggaran melebihi sisa anggaran yang masih ada',
            ]);
        }
    }
}
