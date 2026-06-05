<?php

namespace App\Livewire\Pages\Mutu;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Aplikasi\User;
use App\Models\Bidang;
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

    /** @var int|null */
    public $unitId;

    /** @var string */
    public $statusFilter = 'submitted';

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
            'unitId'       => ['except' => '', 'as' => 'unit'],
            'statusFilter' => ['except' => 'submitted', 'as' => 'status'],
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
        $this->unitId = null;
        $this->statusFilter = 'submitted';
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    public function getUnitProperty(): array
    {
        return Bidang::query()->pluck('nama', 'id')->all();
    }

    public function getCollectionProperty(): LengthAwarePaginator
    {
        return QualityIndicatorRecord::query()
            ->with(['indicator.profile', 'indicator.unit'])
            ->whereBetween('recorded_date', [$this->tglAwal, $this->tglAkhir])
            ->when($this->unitId, function ($query) {
                $query->whereHas('indicator', function ($q) {
                    $q->where('bidang_id', $this->unitId);
                });
            })
            ->when($this->statusFilter && $this->statusFilter !== 'all', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->cari, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('indicator.profile', function ($qp) {
                        $qp->where('title', 'like', '%'.$this->cari.'%');
                    })
                        ->orWhere('notes', 'like', '%'.$this->cari.'%');
                });
            })
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

        $record = QualityIndicatorRecord::where('indicator_id', $indicatorId)
            ->where('recorded_date', $date)
            ->first();

        if ($record) {
            $record->update(['status' => 'approved']);
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

        $record = QualityIndicatorRecord::where('indicator_id', $indicatorId)
            ->where('recorded_date', $date)
            ->first();

        if ($record) {
            $record->update(['status' => 'rejected']);
            $this->flashSuccess('Data penilaian berhasil ditolak.');
        } else {
            $this->flashError('Data tidak ditemukan.');
        }
    }

    public function resetStatus(int $indicatorId, string $date): void
    {
        $record = QualityIndicatorRecord::where('indicator_id', $indicatorId)
            ->where('recorded_date', $date)
            ->first();

        if ($record) {
            $record->update(['status' => 'submitted']);
            $this->flashSuccess('Status data penilaian berhasil di-reset.');
        } else {
            $this->flashError('Data tidak ditemukan.');
        }
    }
}
