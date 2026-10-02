<?php

namespace Tests\Feature\Mutu;

use App\Livewire\Pages\Mutu\ValidasiData;
use App\Models\Quality\QualityIndicator;
use App\Models\Quality\QualityIndicatorRecord;
use App\Models\Quality\QualityIndicatorRecordHistory;
use Database\Factories\Quality\QualityIndicatorFactory;
use Database\Factories\Quality\QualityIndicatorProfileFactory;
use Database\Factories\Quality\QualityIndicatorRecordFactory;
use Livewire\Livewire;
use Livewire\Testing\TestableLivewire;

class ValidasiDataTest extends MutuTestCase
{
    private const TANGGAL = '2026-03-10';

    private const DEP_LAIN = 'ADM';

    /** @var QualityIndicator */
    private $indicator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->indicator = $this->indikator('Indikator Validasi');
    }

    private function indikator(string $title, string $depId = self::DEP_ID): QualityIndicator
    {
        return QualityIndicatorFactory::new()->create([
            'quality_indicator_profile_id' => QualityIndicatorProfileFactory::new()->create(['title' => $title]),
            'dep_id'                       => $depId,
        ]);
    }

    private function recordTersimpan(string $status = 'submitted', array $attributes = []): QualityIndicatorRecord
    {
        return QualityIndicatorRecordFactory::new()->create(array_merge([
            'indicator_id'  => $this->indicator->id,
            'recorded_date' => self::TANGGAL,
            'status'        => $status,
        ], $attributes));
    }

    private function halaman(array $permissions = ['mutu.*']): TestableLivewire
    {
        return Livewire::actingAs($this->createUser(self::NIK, $permissions))
            ->test(ValidasiData::class);
    }

    private function statusRecord(): ?string
    {
        return QualityIndicatorRecord::tanggal($this->indicator->id, self::TANGGAL)->value('status');
    }

    private function historiTerakhir(): ?QualityIndicatorRecordHistory
    {
        return QualityIndicatorRecordHistory::query()
            ->whereHas('record', fn ($q) => $q->tanggal($this->indicator->id, self::TANGGAL))
            ->latest('id')
            ->first();
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: string, 4: string}>
     */
    public function aksiValidasi(): array
    {
        return [
            'approve' => ['approve', 'submitted', 'approved', 'approved', 'Data penilaian berhasil disetujui.'],
            'reject'  => ['reject', 'submitted', 'rejected', 'rejected', 'Data penilaian berhasil ditolak.'],
            'reset'   => ['resetStatus', 'approved', 'submitted', 'reset', 'Status data penilaian berhasil di-reset.'],
        ];
    }

    /**
     * @dataProvider aksiValidasi
     */
    public function test_validator_bisa_mengubah_status_record_dan_tercatat_di_histori(string $aksi, string $statusLama, string $statusBaru, string $aksiHistori, string $pesan): void
    {
        $this->recordTersimpan($statusLama);

        $this->halaman()
            ->set('alasan', 'Alasan validator')
            ->call($aksi, $this->indicator->id, self::TANGGAL)
            ->assertSee($pesan)
            ->assertSeeHtml('alert-success');

        $this->assertSame($statusBaru, $this->statusRecord());

        $histori = $this->historiTerakhir();

        $this->assertNotNull($histori, "Aksi {$aksi} tidak tercatat di histori");
        $this->assertSame($aksiHistori, $histori->action);
        $this->assertSame($statusLama, $histori->status_before);
        $this->assertSame($statusBaru, $histori->status_after);
        $this->assertSame(self::NIK, $histori->actor);
    }

    public function test_alasan_penolakan_tersimpan_di_histori(): void
    {
        $this->recordTersimpan('submitted', ['numerator_value' => 3, 'denominator_value' => 7]);

        $this->halaman()
            ->set('alasan', 'Denominator tidak sesuai register')
            ->call('reject', $this->indicator->id, self::TANGGAL);

        $histori = $this->historiTerakhir();

        $this->assertSame('Denominator tidak sesuai register', $histori->reason);
        $this->assertSame(3, (int) $histori->numerator_value);
        $this->assertSame(7, (int) $histori->denominator_value);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public function alasanTidakValid(): array
    {
        return [
            'kosong'         => ['', 'required'],
            'terlalu pendek' => ['ab', 'min'],
        ];
    }

    /**
     * @dataProvider alasanTidakValid
     */
    public function test_tolak_wajib_disertai_alasan(string $alasan, string $rule): void
    {
        $this->recordTersimpan();

        $this->halaman()
            ->set('alasan', $alasan)
            ->call('reject', $this->indicator->id, self::TANGGAL)
            ->assertHasErrors(['alasan' => $rule]);

        $this->assertSame('submitted', $this->statusRecord());
        $this->assertNull($this->historiTerakhir());
    }

    public function test_tolak_membuka_form_alasan(): void
    {
        $this->recordTersimpan();

        $this->halaman()
            ->call('bukaFormAlasan', 'reject', $this->indicator->id, self::TANGGAL)
            ->assertSet('alasanAksi', 'reject')
            ->assertSet('alasan', '')
            ->assertDispatchedBrowserEvent('open-modal', ['id' => 'modal-alasan-validasi']);
    }

    public function test_simpan_form_alasan_menolak_record_dan_menutup_form(): void
    {
        $this->recordTersimpan();

        $this->halaman()
            ->call('bukaFormAlasan', 'reject', $this->indicator->id, self::TANGGAL)
            ->set('alasan', 'Data ganda')
            ->call('simpanAlasan')
            ->assertDispatchedBrowserEvent('close-modal', ['id' => 'modal-alasan-validasi'])
            ->assertSet('alasanAksi', null);

        $this->assertSame('rejected', $this->statusRecord());
        $this->assertSame('Data ganda', $this->historiTerakhir()->reason);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public function statusBisaDivoid(): array
    {
        return [
            'approved'                 => ['approved'],
            'approved_with_correction' => ['approved_with_correction'],
        ];
    }

    /**
     * @dataProvider statusBisaDivoid
     */
    public function test_validator_bisa_membatalkan_record_disetujui_dengan_alasan(string $status): void
    {
        $this->recordTersimpan($status);

        $this->halaman()
            ->set('alasan', 'Pasien tercatat di unit lain')
            ->call('void', $this->indicator->id, self::TANGGAL)
            ->assertSee('Data penilaian berhasil dibatalkan.')
            ->assertSeeHtml('alert-success');

        $this->assertSame('voided', $this->statusRecord());

        $histori = $this->historiTerakhir();

        $this->assertSame('voided', $histori->action);
        $this->assertSame($status, $histori->status_before);
        $this->assertSame('Pasien tercatat di unit lain', $histori->reason);
    }

    /**
     * @dataProvider alasanTidakValid
     */
    public function test_void_wajib_disertai_alasan(string $alasan, string $rule): void
    {
        $this->recordTersimpan('approved');

        $this->halaman()
            ->set('alasan', $alasan)
            ->call('void', $this->indicator->id, self::TANGGAL)
            ->assertHasErrors(['alasan' => $rule]);

        $this->assertSame('approved', $this->statusRecord());
    }

    /**
     * @return array<string, array{0: string}>
     */
    public function statusTidakBisaDivoid(): array
    {
        return [
            'draft'     => ['draft'],
            'submitted' => ['submitted'],
            'rejected'  => ['rejected'],
            'voided'    => ['voided'],
        ];
    }

    /**
     * @dataProvider statusTidakBisaDivoid
     */
    public function test_void_hanya_untuk_record_disetujui(string $status): void
    {
        $this->recordTersimpan($status);

        $this->halaman()
            ->set('alasan', 'Alasan void')
            ->call('void', $this->indicator->id, self::TANGGAL)
            ->assertSee('Hanya data yang sudah disetujui yang dapat dibatalkan.')
            ->assertSeeHtml('alert-danger');

        $this->assertSame($status, $this->statusRecord());
        $this->assertNull($this->historiTerakhir());
    }

    public function test_void_ditolak_tanpa_izin(): void
    {
        $this->recordTersimpan('approved');

        $this->halaman(['mutu.validasi-data.read'])
            ->set('alasan', 'Alasan void')
            ->call('void', $this->indicator->id, self::TANGGAL)
            ->assertSeeHtml('alert-danger');

        $this->assertSame('approved', $this->statusRecord());
    }

    public function test_void_lewat_form_alasan(): void
    {
        $this->recordTersimpan('approved');

        $this->halaman()
            ->call('bukaFormAlasan', 'void', $this->indicator->id, self::TANGGAL)
            ->assertSet('alasanAksi', 'void')
            ->set('alasan', 'Data ganda')
            ->call('simpanAlasan')
            ->assertDispatchedBrowserEvent('close-modal', ['id' => 'modal-alasan-validasi']);

        $this->assertSame('voided', $this->statusRecord());
    }

    public function test_record_voided_tidak_bisa_dibatalkan_validasinya(): void
    {
        $this->recordTersimpan('voided');

        $this->halaman()
            ->call('resetStatus', $this->indicator->id, self::TANGGAL)
            ->assertSee('Data yang sudah dibatalkan tidak dapat di-reset.')
            ->assertSeeHtml('alert-danger');

        $this->assertSame('voided', $this->statusRecord());
    }

    public function test_batal_validasi_ditolak_tanpa_izin(): void
    {
        $this->recordTersimpan('approved');

        $this->halaman(['mutu.validasi-data.read'])
            ->call('resetStatus', $this->indicator->id, self::TANGGAL)
            ->assertSeeHtml('alert-danger');

        $this->assertSame('approved', $this->statusRecord());
        $this->assertNull($this->historiTerakhir());
    }

    /**
     * @dataProvider aksiValidasi
     */
    public function test_aksi_pada_record_yang_tidak_ada_menghasilkan_pesan_error(string $aksi): void
    {
        $this->halaman()
            ->call($aksi, $this->indicator->id, self::TANGGAL)
            ->assertSee('Data tidak ditemukan.')
            ->assertSeeHtml('alert-danger');
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public function aksiBerizin(): array
    {
        return [
            'approve tanpa izin approve' => ['approve', 'mutu.validasi-data.reject'],
            'reject tanpa izin reject'   => ['reject', 'mutu.validasi-data.approve'],
        ];
    }

    /**
     * @dataProvider aksiBerizin
     */
    public function test_aksi_ditolak_tanpa_izin(string $aksi, string $izinLain): void
    {
        $this->recordTersimpan();

        $this->halaman(['mutu.validasi-data.read', $izinLain])
            ->call($aksi, $this->indicator->id, self::TANGGAL)
            ->assertSeeHtml('alert-danger');

        $this->assertSame('submitted', $this->statusRecord());
    }

    public function test_edit_dan_approve_membuka_modal_koreksi(): void
    {
        $this->recordTersimpan();

        $this->halaman()
            ->call('editAndApprove', $this->indicator->id, self::TANGGAL)
            ->assertEmitted('koreksi-record', $this->indicator->id, self::TANGGAL);
    }

    public function test_edit_dan_approve_ditolak_tanpa_izin_approve(): void
    {
        $this->recordTersimpan();

        $this->halaman(['mutu.validasi-data.read', 'mutu.validasi-data.reject'])
            ->call('editAndApprove', $this->indicator->id, self::TANGGAL)
            ->assertNotEmitted('koreksi-record')
            ->assertSeeHtml('alert-danger');
    }

    public function test_daftar_record_dalam_periode_terurut_dari_tanggal_terbaru(): void
    {
        $this->recordTersimpan('submitted', ['recorded_date' => '2026-03-05', 'notes' => 'Catatan-05']);
        $this->recordTersimpan('submitted', ['recorded_date' => '2026-03-20', 'notes' => 'Catatan-20']);
        $this->recordTersimpan('submitted', ['recorded_date' => '2026-03-12', 'notes' => 'Catatan-12']);
        $this->recordTersimpan('submitted', ['recorded_date' => '2026-02-27', 'notes' => 'Catatan-27-Feb']);
        $this->recordTersimpan('submitted', ['recorded_date' => '2026-04-01', 'notes' => 'Catatan-01-Apr']);

        $this->halaman()
            ->set('tglAwal', '2026-03-01')
            ->set('tglAkhir', '2026-03-31')
            ->call('loadProperties')
            ->assertSeeInOrder(['Catatan-20', 'Catatan-12', 'Catatan-05'])
            ->assertDontSee('Catatan-27-Feb')
            ->assertDontSee('Catatan-01-Apr');
    }

    public function test_cari_beberapa_kata_mencocokkan_judul_dan_catatan(): void
    {
        $lain = $this->indikator('Indikator Departemen Lain');

        $this->recordTersimpan('submitted', ['notes' => 'Record-Cocok pasien jatuh']);
        $this->recordTersimpan('submitted', ['indicator_id' => $lain->id, 'notes' => 'Record-Lain pasien jatuh']);
        $this->recordTersimpan('submitted', ['recorded_date' => '2026-03-11', 'notes' => 'Record-Tanpa-Kata-Kedua']);

        $this->halaman()
            ->set('tglAwal', '2026-03-01')
            ->set('tglAkhir', '2026-03-31')
            ->call('loadProperties')
            ->set('cari', 'validasi jatuh')
            ->assertSee('Record-Cocok')
            ->assertDontSee('Record-Lain')
            ->assertDontSee('Record-Tanpa-Kata-Kedua');
    }

    public function test_badge_status_tampil_dengan_label_dan_warnanya(): void
    {
        $this->recordTersimpan('draft', ['recorded_date' => '2026-03-01']);
        $this->recordTersimpan('submitted', ['recorded_date' => '2026-03-02']);
        $this->recordTersimpan('approved', ['recorded_date' => '2026-03-03']);
        $this->recordTersimpan('rejected', ['recorded_date' => '2026-03-04']);
        $this->recordTersimpan('approved_with_correction', ['recorded_date' => '2026-03-05']);
        $this->recordTersimpan('voided', ['recorded_date' => '2026-03-06']);

        $this->halaman()
            ->set('tglAwal', '2026-03-01')
            ->set('tglAkhir', '2026-03-31')
            ->call('loadProperties')
            ->assertSeeHtml('<span class="badge badge-secondary">Draft</span>')
            ->assertSeeHtml('<span class="badge badge-info">Submitted</span>')
            ->assertSeeHtml('<span class="badge badge-success">Approved</span>')
            ->assertSeeHtml('<span class="badge badge-danger">Rejected</span>')
            ->assertSeeHtml('<span class="badge badge-primary">Approved w/ Correction</span>')
            ->assertSeeHtml('<span class="badge badge-dark">Voided</span>');
    }

    public function test_daftar_record_bisa_difilter_departemen_status_dan_cari(): void
    {
        $lain = $this->indikator('Indikator Departemen Lain', self::DEP_LAIN);

        $this->recordTersimpan('submitted', ['notes' => 'Record-Sendiri-Submitted']);
        $this->recordTersimpan('approved', ['recorded_date' => '2026-03-11', 'notes' => 'Record-Sendiri-Approved']);
        $this->recordTersimpan('submitted', ['indicator_id' => $lain->id, 'notes' => 'Record-Lain-Submitted']);

        $halaman = $this->halaman()
            ->set('tglAwal', '2026-03-01')
            ->set('tglAkhir', '2026-03-31')
            ->call('loadProperties');

        $halaman->set('depId', self::DEP_LAIN)
            ->assertSee('Record-Lain-Submitted')
            ->assertDontSee('Record-Sendiri-Submitted')
            ->assertDontSee('Record-Sendiri-Approved');

        $halaman->set('depId', self::DEP_ID)
            ->set('statusFilter', 'approved')
            ->assertSee('Record-Sendiri-Approved')
            ->assertDontSee('Record-Sendiri-Submitted')
            ->assertDontSee('Record-Lain-Submitted');

        $halaman->set('depId', null)
            ->set('statusFilter', 'all')
            ->set('cari', 'Departemen Lain')
            ->assertSee('Record-Lain-Submitted')
            ->assertDontSee('Record-Sendiri-Submitted');

        $halaman->set('cari', 'Sendiri-Approved')
            ->assertSee('Record-Sendiri-Approved')
            ->assertDontSee('Record-Lain-Submitted');
    }
}
