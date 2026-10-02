<?php

namespace Tests\Feature\Mutu;

use App\Livewire\Pages\Mutu\ValidasiData;
use App\Models\Quality\QualityIndicator;
use App\Models\Quality\QualityIndicatorRecord;
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

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public function aksiValidasi(): array
    {
        return [
            'approve' => ['approve', 'approved', 'Data penilaian berhasil disetujui.'],
            'reject'  => ['reject', 'rejected', 'Data penilaian berhasil ditolak.'],
            'reset'   => ['resetStatus', 'submitted', 'Status data penilaian berhasil di-reset.'],
        ];
    }

    /**
     * @dataProvider aksiValidasi
     */
    public function test_validator_bisa_mengubah_status_record(string $aksi, string $statusBaru, string $pesan): void
    {
        $this->recordTersimpan($aksi === 'resetStatus' ? 'approved' : 'submitted');

        $this->halaman()
            ->call($aksi, $this->indicator->id, self::TANGGAL)
            ->assertSee($pesan)
            ->assertSeeHtml('alert-success');

        $this->assertSame($statusBaru, $this->statusRecord());
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

        $this->recordTersimpan('submitted', ['notes' => 'Catatan pasien jatuh']);
        $this->recordTersimpan('submitted', ['indicator_id' => $lain->id, 'notes' => 'Catatan pasien jatuh di bangsal']);

        $this->halaman()
            ->set('tglAwal', '2026-03-01')
            ->set('tglAkhir', '2026-03-31')
            ->call('loadProperties')
            ->set('cari', 'validasi jatuh')
            ->assertSee('Catatan pasien jatuh')
            ->assertDontSee('Catatan pasien jatuh di bangsal');
    }

    public function test_badge_status_tampil_dengan_label_dan_warnanya(): void
    {
        $this->recordTersimpan('draft', ['recorded_date' => '2026-03-01']);
        $this->recordTersimpan('submitted', ['recorded_date' => '2026-03-02']);
        $this->recordTersimpan('approved', ['recorded_date' => '2026-03-03']);
        $this->recordTersimpan('rejected', ['recorded_date' => '2026-03-04']);
        $this->recordTersimpan('approved_with_correction', ['recorded_date' => '2026-03-05']);

        $this->halaman()
            ->set('tglAwal', '2026-03-01')
            ->set('tglAkhir', '2026-03-31')
            ->call('loadProperties')
            ->assertSeeHtml('<span class="badge badge-secondary">Draft</span>')
            ->assertSeeHtml('<span class="badge badge-info">Submitted</span>')
            ->assertSeeHtml('<span class="badge badge-success">Approved</span>')
            ->assertSeeHtml('<span class="badge badge-danger">Rejected</span>')
            ->assertSeeHtml('<span class="badge badge-primary">Approved w/ Correction</span>');
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
