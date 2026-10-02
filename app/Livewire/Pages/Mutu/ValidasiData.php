<?php

namespace App\Livewire\Pages\Mutu;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Aplikasi\User;
use App\Models\Kepegawaian\Departemen;
use App\Models\Quality\QualityIndicatorRecord;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class ValidasiData extends Component
{
    use DeferredLoading;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string|null */
    public $depId;

    /** @var string */
    public $statusFilter;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected $listeners = [
        'record-saved' => '$refresh',
    ];

    protected function queryString(): array
    {
        return [
            'depId'        => ['except' => '', 'as' => 'dep'],
            'statusFilter' => ['except' => 'all', 'as' => 'status'],
            'tglAwal'      => ['except' => '', 'as' => 'tgl_awal'],
            'tglAkhir'     => ['except' => '', 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.mutu.validasi-data', [
            'records' => $this->isDeferred ? [] : $this->collection,
        ])
            ->layout(BaseLayout::class, ['title' => 'Validasi Data Indikator Mutu']);
    }

    protected function defaultValues(): void
    {
        $this->depId = null;
        $this->statusFilter = 'all';
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    public function getDepartemenProperty(): array
    {
        return Departemen::query()->pluck('nama', 'dep_id')->all();
    }

    public function getCollectionProperty(): LengthAwarePaginator
    {
        return QualityIndicatorRecord::query()
            ->with(['indicator.profile.category', 'indicator.departemen'])
            ->periode($this->tglAwal, $this->tglAkhir)
            ->when($this->depId, fn ($q) => $q->departemen($this->depId))
            ->when($this->statusFilter && $this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->cari, fn ($q) => $q->search($this->cari))
            ->orderBy('recorded_date', 'desc')
            ->paginate($this->perpage);
    }

    public function getRecordersProperty()
    {
        if ($this->isDeferred || $this->collection->isEmpty()) {
            return collect();
        }

        $niks = $this->collection->pluck('recorded_by')->filter()->unique();

        return User::query()
            ->whereIn(DB::raw('trim(pegawai.nik)'), $niks)
            ->get()
            ->keyBy('nik');
    }

    public function approve(int $indicatorId, string $date): void
    {
        if (! auth()->user()->can('mutu.validasi-data.approve')) {
            $this->flashError('Anda tidak memiliki akses untuk menyetujui data.');

            return;
        }

        $record = QualityIndicatorRecord::tanggal($indicatorId, $date)->first();

        if ($record) {
            $record->update(['status' => QualityIndicatorRecord::STATUS_APPROVED]);
            $this->flashSuccess('Data penilaian berhasil disetujui.');
        } else {
            $this->flashError('Data tidak ditemukan.');
        }
    }

    public function reject(int $indicatorId, string $date): void
    {
        if (! auth()->user()->can('mutu.validasi-data.reject')) {
            $this->flashError('Anda tidak memiliki akses untuk menolak data.');

            return;
        }

        $record = QualityIndicatorRecord::tanggal($indicatorId, $date)->first();

        if ($record) {
            $record->update(['status' => QualityIndicatorRecord::STATUS_REJECTED]);
            $this->flashSuccess('Data penilaian berhasil ditolak.');
        } else {
            $this->flashError('Data tidak ditemukan.');
        }
    }

    public function resetStatus(int $indicatorId, string $date): void
    {
        $record = QualityIndicatorRecord::tanggal($indicatorId, $date)->first();

        if ($record) {
            $record->update(['status' => QualityIndicatorRecord::STATUS_SUBMITTED]);
            $this->flashSuccess('Status data penilaian berhasil di-reset.');
        } else {
            $this->flashError('Data tidak ditemukan.');
        }
    }

    public function editAndApprove(int $indicatorId, string $date): void
    {
        if (! auth()->user()->can('mutu.validasi-data.approve')) {
            $this->flashError('Anda tidak memiliki akses untuk melakukan koreksi.');

            return;
        }

        $this->emit('koreksi-record', $indicatorId, $date);
    }
}
