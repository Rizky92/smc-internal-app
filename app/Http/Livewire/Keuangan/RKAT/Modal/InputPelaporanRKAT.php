<?php

namespace App\Http\Livewire\Keuangan\RKAT\Modal;

use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\Models\Keuangan\RKAT\PemakaianAnggaranDetail;
use App\Settings\RKATSettings;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class InputPelaporanRKAT extends Component
{
    use FlashComponent, Filterable, DeferredModal;

    /** @var int */
    public $pemakaianAnggaranId;

    /** @var int */
    public $anggaranBidangId;

    /** @var \Carbon\Carbon|\DateTime|string */
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
        $tahun = app(RKATSettings::class)->tahun;

        return AnggaranBidang::query()
            ->with(['anggaran', 'bidang'])
            ->where('tahun', $tahun)
            ->get()
            ->mapWithKeys(function (AnggaranBidang $ab): array {
                $namaAnggaran = $ab->anggaran->nama;
                $namaBidang = $ab->bidang->nama;
                $tahun = $ab->tahun;

                $string = collect([$namaAnggaran, $namaBidang, $tahun])
                    ->joinStr(' - ')
                    ->value();

                return [$ab->id => $string];
            });
    }

    public function render(): View
    {
        return view('livewire.keuangan.rkat.modal.input-pelaporan-rkat');
    }

    public function prepare(array $options): void
    {
        $this->pemakaianAnggaranId = $options['pemakaianAnggaranId'] ?? -1;
        $this->anggaranBidangId = $options['anggaranBidangId'];
        $this->tglPakai = $options['tglPakai'];
        $this->deskripsi = $options['deskripsi'];

        $detail = PemakaianAnggaranDetail::query()
            ->where('pemakaian_anggaran_id', $this->pemakaianAnggaranId)
            ->get();

        $this->detail = $detail->isNotEmpty()
            ? $detail
                ->map(fn (PemakaianAnggaranDetail $model): array => [
                    'keterangan' => $model->keterangan,
                    'nominal'    => round($model->nominal),
                ])
                ->all()
            : [];
    }

    public function create(): void
    {
        if ($this->isUpdating()) {
            $this->update();

            return;
        }

        if (! Auth::user()->can('keuangan.rkat.pelaporan-rkat.input-laporan-rkat')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $this->validate();
        $this->manuallyValidateAmount();

        tracker_start();

        $pemakaianAnggaran = PemakaianAnggaran::create([
            'judul'              => $this->keterangan,
            'tgl_dipakai'        => $this->tglPakai,
            'anggaran_bidang_id' => $this->anggaranBidangId,
            'user_id'            => Auth::user()->nik,
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
        if (! $this->isUpdating()) {
            $this->create();
        }

        if (! Auth::user()->can('keuangan.rkat.pelaporan-rkat.edit-laporan-rkat')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $this->validate();
        $this->manuallyValidateAmount();

        /** @var \App\Models\Keuangan\RKAT\PemakaianAnggaran */
        $pemakaianAnggaran = PemakaianAnggaran::find($this->pemakaianAnggaranId);

        tracker_start();

        $pemakaianAnggaran->update([
            'judul'              => $this->keterangan,
            'deskripsi'          => $this->deskripsi,
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
        $this->tglPakai = '';
        $this->nominalPemakaian = 0;
        $this->deskripsi = '';
        $this->detail = [[
            'keterangan' => '',
            'nominal'    => 0,
        ]];
    }

    private function manuallyValidateAmount(): void
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
