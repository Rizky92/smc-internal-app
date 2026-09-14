<?php

namespace Tests\Feature\Livewire\Keuangan\Modal;

use App\Livewire\Pages\Keuangan\Modal\InputJurnalPosting;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: staging a jurnal entry into "Jurnal Sementara" before it's actually
 * posted.
 *
 * push() has a second guard beyond the field-level rules: debet and kredit
 * must balance, or nothing gets staged. That guard is invisible to a test
 * that only checks push()'s return value or the jurnalSementara count - the
 * user-facing half of it is the error message actually rendering where
 * <x-form.error> looks for it, which requires asserting on the component's
 * rendered HTML, not just its state.
 */
class InputJurnalPostingTest extends TestCase
{
    /**
     * @test
     *
     * ValidationException::withMessages() in validateBalance() throws under
     * the key 'totalDebetKredit'. input-jurnal-posting.blade.php's
     * <x-form.error> looked for 'totalDebitKredit' (a typo: "Debit" vs
     * "Debet") - a real key that never matched the thrown one, so the error
     * bag always held the message but the view never displayed it. From the
     * browser this read as clicking "Tambah Jurnal" and nothing happening at
     * all: no new row, no visible reason why.
     */
    public function an_unbalanced_entry_is_refused_with_a_visible_reason(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.posting-jurnal.create'], '99999901');

        $component = Livewire::actingAs($petugas)
            ->test(InputJurnalPosting::class)
            ->set('no_bukti', 'BKT-UJI-001')
            ->set('keterangan', 'Uji tidak seimbang')
            ->set('detail', [['kd_rek' => '1110', 'debet' => 100000, 'kredit' => 0]])
            ->call('push')
            ->assertHasErrors('totalDebetKredit')
            ->assertSee('Debet dan Kredit harus sama!');

        $this->assertSame([], $component->get('jurnalSementara'));
    }

    /**
     * @test
     *
     * The counterpart to the above: a balanced entry must actually reach
     * jurnalSementara and reset the detail form back to one blank row.
     */
    public function a_balanced_entry_is_staged_into_jurnal_sementara(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.posting-jurnal.create'], '99999901');

        $component = Livewire::actingAs($petugas)
            ->test(InputJurnalPosting::class)
            ->set('no_bukti', 'BKT-UJI-002')
            ->set('keterangan', 'Uji seimbang')
            ->set('detail', [['kd_rek' => '1110', 'debet' => 100000, 'kredit' => 100000]])
            ->call('push')
            ->assertHasNoErrors()
            ->assertSet('keterangan', '')
            ->assertSet('detail', [['kd_rek' => '', 'debet' => 0, 'kredit' => 0]]);

        $this->assertSame('BKT-UJI-002', $component->get('jurnalSementara')[0]['no_bukti']);
    }
}
