<?php

namespace App\Livewire\Pages\Mutu;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Aplikasi\User;
use App\Models\Kepegawaian\Departemen;
use App\Models\Quality\QualityIndicatorCorrectionRequest;
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
    private const AKSI_BERALASAN = ['reject', 'void', 'tolakKoreksi'];

    /**
     * Nilai filter status (bukan status record) untuk record yang punya pengajuan koreksi pending.
     */
    public const FILTER_KOREKSI = 'koreksi';

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
            'records'         => $this->isDeferred ? [] : $this->collection,
            'judulFormAlasan' => [
                'reject'       => 'Alasan Penolakan',
                'void'         => 'Alasan Pembatalan (Void)',
                'tolakKoreksi' => 'Alasan Penolakan Koreksi',
            ][(string) $this->alasanAksi] ?? 'Alasan',
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
            ->with('pendingCorrection')
            ->withCount('histories')
            ->periode($this->tglAwal, $this->tglAkhir)
            ->when($this->depId, fn ($q) => $q->departemen($this->depId))
            ->when($this->statusFilter === self::FILTER_KOREKSI, fn ($q) => $q->has('pendingCorrection'))
            ->when($this->statusFilter && ! in_array($this->statusFilter, ['all', self::FILTER_KOREKSI], true), fn ($q) => $q->where('status', $this->statusFilter))
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

        if (! $this->menungguValidasi($record)) {
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

        if (! $this->menungguValidasi($record)) {
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

        if (! in_array($record->status, QualityIndicatorRecord::STATUSES_BISA_DIRESET, true)) {
            $this->flashError('Hanya data yang sudah divalidasi yang dapat di-reset.');

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
        tracker_start('mysql_smc');

        DB::connection('mysql_smc')->transaction(function () use ($record, $status, $aksiHistori, $alasan): void {
            $statusLama = $record->status;

            $record->update(['status' => $status]);
            $record->recordHistory($aksiHistori, $statusLama, $alasan);

            // Pengajuan koreksi hanya berlaku selama data masih berstatus disetujui.
            if (! in_array($status, QualityIndicatorRecord::STATUSES_DISETUJUI, true)) {
                $record->pendingCorrection()->update([
                    'status'        => QualityIndicatorCorrectionRequest::STATUS_REJECTED,
                    'reviewed_by'   => user()->nik,
                    'review_reason' => 'Ditutup otomatis karena status data berubah menjadi '.$record->statusLabel().'.',
                ]);
            }
        });

        tracker_end('mysql_smc');
    }

    public function setujuiKoreksi(int $indicatorId, string $date): void
    {
        if (! auth()->user()->can('mutu.validasi-data.approve')) {
            $this->flashError('Anda tidak memiliki akses untuk menyetujui koreksi.');

            return;
        }

        $record = QualityIndicatorRecord::tanggal($indicatorId, $date)->first();
        $pengajuan = $record ? $record->pendingCorrection()->first() : null;

        if (! $record || ! $pengajuan) {
            $this->flashError('Tidak ada pengajuan koreksi yang menunggu validasi.');

            return;
        }

        tracker_start('mysql_smc');

        $diproses = DB::connection('mysql_smc')->transaction(function () use ($record, $pengajuan): bool {
            $pengajuan = $this->kunciPengajuanPending($pengajuan);

            if (! $pengajuan) {
                return false;
            }

            $statusLama = $record->status;
            $nilaiLama = $record->only(['numerator_value', 'denominator_value', 'notes']);

            $record->update([
                'numerator_value'   => $pengajuan->numerator_value,
                'denominator_value' => $pengajuan->denominator_value,
                'notes'             => $pengajuan->notes,
                'status'            => QualityIndicatorRecord::STATUS_APPROVED_WITH_CORRECTION,
            ]);

            foreach ($nilaiLama as $field => $lama) {
                if ((string) $lama === (string) $record->{$field}) {
                    continue;
                }

                $record->auditLogs()->create([
                    'field_name'    => $field,
                    'old_value'     => (string) $lama,
                    'new_value'     => (string) $record->{$field},
                    'changed_by'    => user()->nik,
                    'reason'        => $pengajuan->reason,
                    'recorded_date' => $record->recorded_date,
                ]);
            }

            $pengajuan->update([
                'status'      => QualityIndicatorCorrectionRequest::STATUS_APPROVED,
                'reviewed_by' => user()->nik,
            ]);

            $record->recordHistory(QualityIndicatorRecordHistory::ACTION_CORRECTION_APPROVED, $statusLama, $pengajuan->reason);

            return true;
        });

        tracker_end('mysql_smc');

        if (! $diproses) {
            $this->flashError('Tidak ada pengajuan koreksi yang menunggu validasi.');

            return;
        }

        $this->flashSuccess('Pengajuan koreksi berhasil disetujui.');
    }

    public function tolakKoreksi(int $indicatorId, string $date): void
    {
        if (! auth()->user()->can('mutu.validasi-data.reject')) {
            $this->flashError('Anda tidak memiliki akses untuk menolak koreksi.');

            return;
        }

        $record = QualityIndicatorRecord::tanggal($indicatorId, $date)->first();
        $pengajuan = $record ? $record->pendingCorrection()->first() : null;

        if (! $record || ! $pengajuan) {
            $this->flashError('Tidak ada pengajuan koreksi yang menunggu validasi.');

            return;
        }

        $this->validate(['alasan' => ['required', 'string', 'min:3']]);

        tracker_start('mysql_smc');

        $diproses = DB::connection('mysql_smc')->transaction(function () use ($record, $pengajuan): bool {
            $pengajuan = $this->kunciPengajuanPending($pengajuan);

            if (! $pengajuan) {
                return false;
            }

            $pengajuan->update([
                'status'        => QualityIndicatorCorrectionRequest::STATUS_REJECTED,
                'reviewed_by'   => user()->nik,
                'review_reason' => $this->alasan,
            ]);

            $record->recordHistory(QualityIndicatorRecordHistory::ACTION_CORRECTION_REJECTED, $record->status, $this->alasan);

            return true;
        });

        tracker_end('mysql_smc');

        $this->tutupFormAlasan();

        if (! $diproses) {
            $this->flashError('Tidak ada pengajuan koreksi yang menunggu validasi.');

            return;
        }

        $this->flashSuccess('Pengajuan koreksi berhasil ditolak.');
    }

    /**
     * Kunci baris pengajuan dan pastikan masih pending, agar dua validator tidak memproses pengajuan yang sama.
     */
    protected function kunciPengajuanPending(QualityIndicatorCorrectionRequest $pengajuan): ?QualityIndicatorCorrectionRequest
    {
        /** @var QualityIndicatorCorrectionRequest|null */
        $terkunci = QualityIndicatorCorrectionRequest::query()
            ->whereKey($pengajuan->id)
            ->where('status', QualityIndicatorCorrectionRequest::STATUS_PENDING)
            ->lockForUpdate()
            ->first();

        return $terkunci;
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

        $record = QualityIndicatorRecord::tanggal($indicatorId, $date)->first();

        if (! $record || $record->status !== QualityIndicatorRecord::STATUS_SUBMITTED) {
            $this->flashError('Koreksi langsung hanya untuk data yang menunggu validasi.');

            return;
        }

        $this->emit('koreksi-record', $indicatorId, $date);
    }

    /**
     * Approve dan reject hanya untuk data yang diserahkan; data disetujui diubah lewat koreksi atau void (ADR 0002).
     */
    protected function menungguValidasi(QualityIndicatorRecord $record): bool
    {
        if ($record->status === QualityIndicatorRecord::STATUS_SUBMITTED) {
            return true;
        }

        $this->flashError('Hanya data yang menunggu validasi yang dapat disetujui atau ditolak.');

        return false;
    }
}
