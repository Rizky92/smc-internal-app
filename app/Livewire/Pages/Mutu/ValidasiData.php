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
use App\Models\Quality\QualityIndicatorRecordHistory;
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

    /**
     * Aksi validator yang wajib disertai alasan; nilainya adalah nama method aksi.
     */
    private const AKSI_BERALASAN = ['reject', 'void'];

    /** @var string|null */
    public $alasanAksi;

    /** @var int|null */
    public $alasanIndicatorId;

    /** @var string|null */
    public $alasanDate;

    /** @var string */
    public $alasan = '';

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
            ->withCount('histories')
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

    /**
     * Buka form alasan untuk aksi validator yang wajib beralasan.
     */
    public function bukaFormAlasan(string $aksi, int $indicatorId, string $date): void
    {
        if (! in_array($aksi, self::AKSI_BERALASAN, true)) {
            return;
        }

        $this->resetErrorBag();

        $this->alasanAksi = $aksi;
        $this->alasanIndicatorId = $indicatorId;
        $this->alasanDate = $date;
        $this->alasan = '';

        $this->dispatchBrowserEvent('open-modal', ['id' => 'modal-alasan-validasi']);
    }

    public function simpanAlasan(): void
    {
        if (! in_array($this->alasanAksi, self::AKSI_BERALASAN, true)) {
            return;
        }

        $this->{$this->alasanAksi}((int) $this->alasanIndicatorId, (string) $this->alasanDate);
    }

    public function approve(int $indicatorId, string $date): void
    {
        if (! auth()->user()->can('mutu.validasi-data.approve')) {
            $this->flashError('Anda tidak memiliki akses untuk menyetujui data.');

            return;
        }

        $record = QualityIndicatorRecord::tanggal($indicatorId, $date)->first();

        if (! $record) {
            $this->flashError('Data tidak ditemukan.');

            return;
        }

        $this->ubahStatus($record, QualityIndicatorRecord::STATUS_APPROVED, QualityIndicatorRecordHistory::ACTION_APPROVED);

        $this->flashSuccess('Data penilaian berhasil disetujui.');
    }

    public function reject(int $indicatorId, string $date): void
    {
        if (! auth()->user()->can('mutu.validasi-data.reject')) {
            $this->flashError('Anda tidak memiliki akses untuk menolak data.');

            return;
        }

        $record = QualityIndicatorRecord::tanggal($indicatorId, $date)->first();

        if (! $record) {
            $this->flashError('Data tidak ditemukan.');

            return;
        }

        $this->validate(['alasan' => ['required', 'string', 'min:3']]);

        $this->ubahStatus($record, QualityIndicatorRecord::STATUS_REJECTED, QualityIndicatorRecordHistory::ACTION_REJECTED, $this->alasan);

        $this->tutupFormAlasan();
        $this->flashSuccess('Data penilaian berhasil ditolak.');
    }

    public function resetStatus(int $indicatorId, string $date): void
    {
        if (! auth()->user()->canAny(['mutu.validasi-data.approve', 'mutu.validasi-data.reject'])) {
            $this->flashError('Anda tidak memiliki akses untuk membatalkan validasi.');

            return;
        }

        $record = QualityIndicatorRecord::tanggal($indicatorId, $date)->first();

        if (! $record) {
            $this->flashError('Data tidak ditemukan.');

            return;
        }

        if ($record->status === QualityIndicatorRecord::STATUS_VOIDED) {
            $this->flashError('Data yang sudah dibatalkan tidak dapat di-reset.');

            return;
        }

        $this->ubahStatus($record, QualityIndicatorRecord::STATUS_SUBMITTED, QualityIndicatorRecordHistory::ACTION_RESET);

        $this->flashSuccess('Status data penilaian berhasil di-reset.');
    }

    /**
     * Batalkan data yang sudah disetujui tanpa menghapusnya (ADR 0002).
     */
    public function void(int $indicatorId, string $date): void
    {
        if (! auth()->user()->canAny(['mutu.validasi-data.approve', 'mutu.validasi-data.reject'])) {
            $this->flashError('Anda tidak memiliki akses untuk membatalkan data.');

            return;
        }

        $record = QualityIndicatorRecord::tanggal($indicatorId, $date)->first();

        if (! $record) {
            $this->flashError('Data tidak ditemukan.');

            return;
        }

        if (! in_array($record->status, QualityIndicatorRecord::STATUSES_BISA_DIVOID, true)) {
            $this->flashError('Hanya data yang sudah disetujui yang dapat dibatalkan.');

            return;
        }

        $this->validate(['alasan' => ['required', 'string', 'min:3']]);

        $this->ubahStatus($record, QualityIndicatorRecord::STATUS_VOIDED, QualityIndicatorRecordHistory::ACTION_VOIDED, $this->alasan);

        $this->tutupFormAlasan();
        $this->flashSuccess('Data penilaian berhasil dibatalkan.');
    }

    protected function ubahStatus(QualityIndicatorRecord $record, string $status, string $aksiHistori, ?string $alasan = null): void
    {
        DB::connection('mysql_smc')->transaction(function () use ($record, $status, $aksiHistori, $alasan): void {
            $statusLama = $record->status;

            $record->update(['status' => $status]);
            $record->recordHistory($aksiHistori, $statusLama, $alasan);
        });
    }

    protected function tutupFormAlasan(): void
    {
        $this->dispatchBrowserEvent('close-modal', ['id' => 'modal-alasan-validasi']);
        $this->reset(['alasanAksi', 'alasanIndicatorId', 'alasanDate', 'alasan']);
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
