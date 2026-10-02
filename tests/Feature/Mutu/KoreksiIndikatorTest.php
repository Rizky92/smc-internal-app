<?php

namespace Tests\Feature\Mutu;

use App\Livewire\Pages\Mutu\Modal\InputKoreksiIndikator;
use App\Livewire\Pages\Mutu\Modal\ViewAuditLogIndikator;
use App\Models\Quality\IndicatorAuditLog;
use App\Models\Quality\QualityIndicator;
use App\Models\Quality\QualityIndicatorRecord;
use Database\Factories\Quality\QualityIndicatorFactory;
use Database\Factories\Quality\QualityIndicatorRecordFactory;
use Livewire\Livewire;
use Livewire\Testing\TestableLivewire;

class KoreksiIndikatorTest extends MutuTestCase
{
    private const TANGGAL = '2026-03-10';

    /** @var QualityIndicator */
    private $indicator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->indicator = QualityIndicatorFactory::new()->create();

        QualityIndicatorRecordFactory::new()->create([
            'indicator_id'      => $this->indicator->id,
            'recorded_date'     => self::TANGGAL,
            'numerator_value'   => 4,
            'denominator_value' => 5,
            'notes'             => 'Catatan awal',
            'status'            => 'submitted',
        ]);
    }

    private function koreksi(array $permissions = ['mutu.*']): TestableLivewire
    {
        return Livewire::actingAs($this->createUser(self::NIK, $permissions))
            ->test(InputKoreksiIndikator::class)
            ->call('loadRecord', $this->indicator->id, self::TANGGAL);
    }

    private function record(): QualityIndicatorRecord
    {
        return QualityIndicatorRecord::tanggal($this->indicator->id, self::TANGGAL)->firstOrFail();
    }

    private function auditLog(): array
    {
        return IndicatorAuditLog::query()
            ->where('indicator_id', $this->indicator->id)
            ->orderBy('field_name')
            ->get(['field_name', 'old_value', 'new_value', 'changed_by', 'reason'])
            ->toArray();
    }

    public function test_modal_koreksi_terisi_nilai_record(): void
    {
        $this->koreksi()
            ->assertSet('numeratorValue', 4)
            ->assertSet('denominatorValue', 5)
            ->assertSet('notes', 'Catatan awal')
            ->assertSet('reason', '')
            ->assertDispatchedBrowserEvent('open-modal', ['id' => 'modal-input-koreksi-indikator']);
    }

    public function test_validator_bisa_mengoreksi_dan_menyetujui_record(): void
    {
        $this->koreksi()
            ->set('numeratorValue', 3)
            ->set('notes', 'Catatan dikoreksi')
            ->set('reason', 'Salah hitung')
            ->call('save')
            ->assertHasNoErrors()
            ->assertEmitted('record-saved');

        $record = $this->record();

        $this->assertSame('approved_with_correction', $record->status);
        $this->assertSame(3, (int) $record->numerator_value);
        $this->assertSame(5, (int) $record->denominator_value);
        $this->assertSame('Catatan dikoreksi', $record->notes);
    }

    public function test_hanya_field_yang_berubah_dicatat_di_audit_log(): void
    {
        $this->koreksi()
            ->set('numeratorValue', 3)
            ->set('notes', 'Catatan dikoreksi')
            ->set('reason', 'Salah hitung')
            ->call('save');

        $this->assertSame([
            [
                'field_name' => 'notes',
                'old_value'  => 'Catatan awal',
                'new_value'  => 'Catatan dikoreksi',
                'changed_by' => self::NIK,
                'reason'     => 'Salah hitung',
            ],
            [
                'field_name' => 'numerator_value',
                'old_value'  => '4',
                'new_value'  => '3',
                'changed_by' => self::NIK,
                'reason'     => 'Salah hitung',
            ],
        ], $this->auditLog());
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public function alasanTidakValid(): array
    {
        return [
            'alasan kosong'  => ['', 'required'],
            'alasan pendek'  => ['ab', 'min'],
        ];
    }

    /**
     * @dataProvider alasanTidakValid
     */
    public function test_koreksi_wajib_disertai_alasan(string $reason, string $rule): void
    {
        $this->koreksi()
            ->set('numeratorValue', 3)
            ->set('reason', $reason)
            ->call('save')
            ->assertHasErrors(['reason' => $rule])
            ->assertNotEmitted('record-saved');

        $this->assertSame('submitted', $this->record()->status);
        $this->assertSame([], $this->auditLog());
    }

    public function test_koreksi_ditolak_tanpa_izin_approve(): void
    {
        $this->koreksi(['mutu.validasi-data.read', 'mutu.validasi-data.reject'])
            ->set('numeratorValue', 3)
            ->set('reason', 'Salah hitung')
            ->call('save')
            ->assertNotEmitted('record-saved')
            ->assertSee('Anda tidak memiliki akses untuk melakukan koreksi.');

        $this->assertSame('submitted', $this->record()->status);
        $this->assertSame(4, (int) $this->record()->numerator_value);
        $this->assertSame([], $this->auditLog());
    }

    public function test_audit_log_menampilkan_riwayat_koreksi_tanggal_itu_secara_berurutan(): void
    {
        $log = fn (string $date, string $reason, string $at) => IndicatorAuditLog::forceCreate([
            'indicator_id'  => $this->indicator->id,
            'recorded_date' => $date,
            'field_name'    => 'numerator_value',
            'old_value'     => '1',
            'new_value'     => '2',
            'changed_by'    => self::NIK,
            'reason'        => $reason,
            'created_at'    => $at,
        ]);

        $log(self::TANGGAL, 'Koreksi-Kedua', '2026-03-12 09:00:00');
        $log(self::TANGGAL, 'Koreksi-Pertama', '2026-03-11 09:00:00');
        $log('2026-03-11', 'Koreksi-Tanggal-Lain', '2026-03-11 10:00:00');

        Livewire::actingAs($this->createUser())
            ->test(ViewAuditLogIndikator::class)
            ->call('loadLogs', $this->indicator->id, self::TANGGAL)
            ->assertDispatchedBrowserEvent('open-modal', ['id' => 'modal-view-audit-log-indikator'])
            ->assertSeeInOrder(['Koreksi-Pertama', 'Koreksi-Kedua'])
            ->assertDontSee('Koreksi-Tanggal-Lain');
    }

    public function test_audit_log_record_yang_tidak_ada_menghasilkan_pesan_error(): void
    {
        Livewire::actingAs($this->createUser())
            ->test(ViewAuditLogIndikator::class)
            ->call('loadLogs', $this->indicator->id, '2026-01-01')
            ->assertNotDispatchedBrowserEvent('open-modal')
            ->assertSee('Data tidak ditemukan.');
    }
}
