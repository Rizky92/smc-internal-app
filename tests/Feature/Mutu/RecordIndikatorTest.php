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
        return QualityIndicatorRecord::tanggal($this->indicator->id, self::TANGGAL)->first();
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

    public function test_nik_perekam_tersimpan_utuh(): void
    {
        $this->modal()
            ->set('recordedDate', self::TANGGAL)
            ->set('numeratorValue', 8)
            ->set('denominatorValue', 10)
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertSame(self::NIK, $this->record()->recorded_by);
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
            'submitted'                => ['submitted'],
            'approved'                 => ['approved'],
            'approved_with_correction' => ['approved_with_correction'],
            'voided'                   => ['voided'],
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

    /**
     * @return array<string, array{0: string, 1: bool}>
     */
    public function isianPerStatus(): array
    {
        return [
            'draft'                    => ['draft', false],
            'submitted'                => ['submitted', true],
            'approved'                 => ['approved', true],
            'rejected'                 => ['rejected', false],
            'approved_with_correction' => ['approved_with_correction', true],
            'voided'                   => ['voided', true],
        ];
    }

    /**
     * @dataProvider isianPerStatus
     */
    public function test_isian_modal_dikunci_sesuai_status_record(string $status, bool $terkunci): void
    {
        $this->recordTersimpan($status);

        $html = $this->modal(self::TANGGAL)->lastRenderedDom;

        $numeratorTerkunci = (bool) preg_match('/wire:model\.defer="numeratorValue"[^>]*\bdisabled\b/', $html);

        $this->assertSame($terkunci, $numeratorTerkunci, "Isian numerator untuk record {$status}");
    }

    public function test_submit_mencatat_histori(): void
    {
        $this->modal()
            ->set('recordedDate', self::TANGGAL)
            ->set('numeratorValue', 2)
            ->set('denominatorValue', 5)
            ->call('submit');

        $histori = $this->record()->histories()->get();

        $this->assertCount(1, $histori);
        $this->assertSame('submitted', $histori[0]->action);
        $this->assertNull($histori[0]->status_before);
        $this->assertSame('submitted', $histori[0]->status_after);
        $this->assertSame(self::NIK, $histori[0]->actor);
    }

    public function test_simpan_draft_tidak_mencatat_histori(): void
    {
        $this->modal()
            ->set('recordedDate', self::TANGGAL)
            ->call('save');

        $this->assertSame(0, $this->record()->histories()->count());
    }

    public function test_record_ditolak_bisa_diperbaiki_dan_disubmit_ulang_pada_record_yang_sama(): void
    {
        $id = $this->recordTersimpan('rejected')->id;

        $this->modal(self::TANGGAL)
            ->set('numeratorValue', 5)
            ->call('submit')
            ->assertEmitted('record-saved');

        $this->assertSame(1, QualityIndicatorRecord::where('indicator_id', $this->indicator->id)->count());

        $record = $this->record();

        $this->assertSame($id, $record->id);
        $this->assertSame('submitted', $record->status);
        $this->assertSame(5, (int) $record->numerator_value);

        $histori = $record->histories()->latest('id')->first();

        $this->assertSame('submitted', $histori->action);
        $this->assertSame('rejected', $histori->status_before);
        $this->assertSame(5, (int) $histori->numerator_value);
    }

    public function test_alasan_penolakan_terakhir_tampil_di_modal(): void
    {
        $record = $this->recordTersimpan('rejected');

        $tolak = fn (string $reason, string $at) => $record->histories()->forceCreate([
            'action'            => 'rejected',
            'status_before'     => 'submitted',
            'status_after'      => 'rejected',
            'numerator_value'   => 4,
            'denominator_value' => 5,
            'reason'            => $reason,
            'actor'             => self::NIK,
            'created_at'        => $at,
        ]);

        $tolak('Alasan-Lama', '2026-03-11 09:00:00');
        $tolak('Alasan-Terbaru', '2026-03-12 09:00:00');

        $this->modal(self::TANGGAL)
            ->assertSet('alasanPenolakan', 'Alasan-Terbaru')
            ->assertSee('Alasan-Terbaru')
            ->assertDontSee('Alasan-Lama');
    }

    public function test_status_kunci_dibaca_dari_database_bukan_dari_klien(): void
    {
        $this->recordTersimpan('submitted');

        $this->modal(self::TANGGAL)
            ->set('status', 'draft')
            ->set('numeratorValue', 1)
            ->call('save')
            ->assertNotEmitted('record-saved');

        $this->assertSame('submitted', $this->record()->status);
        $this->assertSame(4, (int) $this->record()->numerator_value);
    }

    public function test_petugas_bisa_menghapus_draft(): void
    {
        $this->recordTersimpan('draft');

        $this->modal(self::TANGGAL)
            ->call('delete')
            ->assertEmitted('record-saved');

        $this->assertNull($this->record());
    }

    private function recordPernahDiserahkan(string $status): QualityIndicatorRecord
    {
        $record = $this->recordTersimpan($status);

        $record->histories()->forceCreate([
            'action'            => 'rejected',
            'status_before'     => 'submitted',
            'status_after'      => 'rejected',
            'numerator_value'   => 4,
            'denominator_value' => 5,
            'reason'            => 'Ditolak',
            'actor'             => self::NIK,
        ]);

        return $record;
    }

    /**
     * @return array<string, array{0: string}>
     */
    public function statusPernahDiserahkan(): array
    {
        return [
            'rejected'                   => ['rejected'],
            'draft setelah ditolak'      => ['draft'],
        ];
    }

    /**
     * @dataProvider statusPernahDiserahkan
     */
    public function test_record_yang_pernah_diserahkan_tidak_bisa_dihapus_dari_modal(string $status): void
    {
        $this->recordPernahDiserahkan($status);

        $this->modal(self::TANGGAL)
            ->call('delete')
            ->assertNotEmitted('record-saved')
            ->assertSee('Data yang pernah diserahkan tidak dapat dihapus.')
            ->assertSeeHtml('alert-danger');

        $this->assertNotNull($this->record());
    }

    /**
     * @dataProvider statusPernahDiserahkan
     */
    public function test_record_yang_pernah_diserahkan_tidak_bisa_dihapus_dari_halaman_detail(string $status): void
    {
        $this->recordPernahDiserahkan($status);

        Livewire::actingAs($this->createUser())
            ->test(DetailIndikatorMutu::class, ['indicatorId' => $this->indicator->id])
            ->call('deleteRecord', self::TANGGAL)
            ->assertSee('Data yang pernah diserahkan tidak dapat dihapus.');

        $this->assertNotNull($this->record());
    }

    public function test_tombol_hapus_hanya_tampil_untuk_draft_yang_belum_pernah_diserahkan(): void
    {
        $this->recordTersimpan('draft');

        $this->modal(self::TANGGAL)->assertSeeHtml('wire:click="delete"');

        $this->record()->delete();
        $this->recordPernahDiserahkan('rejected');

        $this->modal(self::TANGGAL)->assertDontSeeHtml('wire:click="delete"');
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

    /**
     * @dataProvider statusTerkunci
     */
    public function test_record_terkunci_tidak_bisa_dihapus_dari_halaman_detail(string $status): void
    {
        $this->recordTersimpan($status);

        Livewire::actingAs($this->createUser())
            ->test(DetailIndikatorMutu::class, ['indicatorId' => $this->indicator->id])
            ->call('deleteRecord', self::TANGGAL)
            ->assertSee('Data telah dikunci dan tidak dapat dihapus.');

        $this->assertNotNull($this->record(), "Record {$status} terhapus dari halaman detail");
    }

    public function test_hapus_record_yang_tidak_ada_dari_halaman_detail_menampilkan_error(): void
    {
        Livewire::actingAs($this->createUser())
            ->test(DetailIndikatorMutu::class, ['indicatorId' => $this->indicator->id])
            ->call('deleteRecord', self::TANGGAL)
            ->assertSee('Data penilaian tidak ditemukan.')
            ->assertSeeHtml('alert-danger')
            ->assertDontSee('Data penilaian berhasil dihapus.');
    }
}
