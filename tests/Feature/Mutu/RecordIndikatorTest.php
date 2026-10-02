<?php

namespace Tests\Feature\Mutu;

use App\Livewire\Pages\Mutu\DetailIndikatorMutu;
use App\Livewire\Pages\Mutu\Modal\InputRecordIndikator;
use App\Models\Quality\QualityIndicator;
use App\Models\Quality\QualityIndicatorRecord;
use Database\Factories\Quality\QualityIndicatorFactory;
use Database\Factories\Quality\QualityIndicatorRecordFactory;
use Livewire\Livewire;
use Livewire\Testing\TestableLivewire;

class RecordIndikatorTest extends MutuTestCase
{
    private const TANGGAL = '2026-03-10';

    /** @var QualityIndicator */
    private $indicator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->indicator = QualityIndicatorFactory::new()->create();
    }

    private function modal(?string $date = null): TestableLivewire
    {
        return Livewire::actingAs($this->createUser())
            ->test(InputRecordIndikator::class)
            ->call('loadIndicator', $this->indicator->id, $date);
    }

    private function record(): ?QualityIndicatorRecord
    {
        return QualityIndicatorRecord::query()
            ->where('indicator_id', $this->indicator->id)
            ->where('recorded_date', self::TANGGAL)
            ->first();
    }

    private function recordTersimpan(string $status, array $attributes = []): QualityIndicatorRecord
    {
        return QualityIndicatorRecordFactory::new()->create(array_merge([
            'indicator_id'      => $this->indicator->id,
            'recorded_date'     => self::TANGGAL,
            'numerator_value'   => 4,
            'denominator_value' => 5,
            'notes'             => 'Catatan awal',
            'status'            => $status,
        ], $attributes));
    }

    public function test_form_baru_terisi_tanggal_hari_ini_dengan_nilai_nol(): void
    {
        $this->modal()
            ->assertSet('recordedDate', now()->format('Y-m-d'))
            ->assertSet('numeratorValue', 0)
            ->assertSet('denominatorValue', 0)
            ->assertSet('status', 'draft')
            ->assertSet('isEdit', false)
            ->assertDispatchedBrowserEvent('open-modal');
    }

    public function test_petugas_bisa_menyimpan_record_sebagai_draft(): void
    {
        $this->modal()
            ->set('recordedDate', self::TANGGAL)
            ->set('numeratorValue', 8)
            ->set('denominatorValue', 10)
            ->set('notes', 'Observasi pagi')
            ->call('save')
            ->assertHasNoErrors()
            ->assertEmitted('record-saved');

        $record = $this->record();

        $this->assertNotNull($record);
        $this->assertSame('draft', $record->status);
        $this->assertSame(8, (int) $record->numerator_value);
        $this->assertSame(10, (int) $record->denominator_value);
        $this->assertSame('Observasi pagi', $record->notes);
    }

    public function test_petugas_bisa_membuka_draft_dan_melihat_nilai_tersimpan(): void
    {
        $this->recordTersimpan('draft');

        $this->modal(self::TANGGAL)
            ->assertSet('recordedDate', self::TANGGAL)
            ->assertSet('numeratorValue', 4)
            ->assertSet('denominatorValue', 5)
            ->assertSet('notes', 'Catatan awal')
            ->assertSet('status', 'draft')
            ->assertSet('isEdit', true);
    }

    public function test_menyimpan_ulang_di_tanggal_yang_sama_memperbarui_record(): void
    {
        $this->recordTersimpan('draft');

        $this->modal(self::TANGGAL)
            ->set('numeratorValue', 9)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(1, QualityIndicatorRecord::where('indicator_id', $this->indicator->id)->count());
        $this->assertSame(9, (int) $this->record()->numerator_value);
    }

    public function test_petugas_bisa_submit_record_sehingga_terkunci(): void
    {
        $this->modal()
            ->set('recordedDate', self::TANGGAL)
            ->set('numeratorValue', 8)
            ->set('denominatorValue', 10)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertEmitted('record-saved');

        $this->assertSame('submitted', $this->record()->status);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public function statusTerkunci(): array
    {
        return [
            'submitted' => ['submitted'],
            'approved'  => ['approved'],
        ];
    }

    /**
     * @dataProvider statusTerkunci
     */
    public function test_record_terkunci_tidak_bisa_disimpan_disubmit_atau_dihapus(string $status): void
    {
        $this->recordTersimpan($status);

        foreach (['save', 'submit', 'delete'] as $action) {
            $this->modal(self::TANGGAL)
                ->set('numeratorValue', 1)
                ->call($action)
                ->assertNotEmitted('record-saved');

            $record = $this->record();

            $this->assertNotNull($record, "Record {$status} terhapus lewat {$action}");
            $this->assertSame($status, $record->status);
            $this->assertSame(4, (int) $record->numerator_value);
        }
    }

    public function test_petugas_bisa_menghapus_draft(): void
    {
        $this->recordTersimpan('draft');

        $this->modal(self::TANGGAL)
            ->call('delete')
            ->assertEmitted('record-saved');

        $this->assertNull($this->record());
    }

    /**
     * @return array<string, array{0: string, 1: mixed, 2: string}>
     */
    public function isianTidakValid(): array
    {
        return [
            'tanggal kosong'        => ['recordedDate', '', 'required'],
            'tanggal tidak valid'   => ['recordedDate', 'bukan-tanggal', 'date'],
            'numerator negatif'     => ['numeratorValue', -1, 'min'],
            'numerator bukan angka' => ['numeratorValue', 'abc', 'integer'],
            'denominator negatif'   => ['denominatorValue', -1, 'min'],
            'denominator kosong'    => ['denominatorValue', '', 'required'],
        ];
    }

    /**
     * @dataProvider isianTidakValid
     *
     * @param  mixed  $value
     */
    public function test_isian_record_divalidasi(string $field, $value, string $rule): void
    {
        $this->modal()
            ->set('recordedDate', self::TANGGAL)
            ->set($field, $value)
            ->call('save')
            ->assertHasErrors([$field => $rule])
            ->assertNotEmitted('record-saved');

        $this->assertSame(0, QualityIndicatorRecord::where('indicator_id', $this->indicator->id)->count());
    }

    public function test_record_bisa_dihapus_dari_halaman_detail(): void
    {
        $this->recordTersimpan('draft');

        Livewire::actingAs($this->createUser())
            ->test(DetailIndikatorMutu::class, ['indicatorId' => $this->indicator->id])
            ->call('deleteRecord', self::TANGGAL);

        $this->assertNull($this->record());
    }
}
