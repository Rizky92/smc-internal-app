<?php

namespace Tests\Feature\Livewire\Keuangan\Modal;

use App\Livewire\Pages\Keuangan\Modal\InputJurnalPosting;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Jurnal\PostingJurnal;
use Illuminate\Support\Facades\DB;
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

    /**
     * Posting writes to three places across two connections — jurnal and
     * detailjurnal in Khanza, posting_jurnal in SIAP — so cleanup has to find
     * every row by the no_bukti the tests write under.
     */
    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');

        $noJurnal = $sik->table('jurnal')->where('no_bukti', 'like', 'BKT-UJI%')->pluck('no_jurnal');

        $sik->table('detailjurnal')->whereIn('no_jurnal', $noJurnal)->delete();
        $sik->table('jurnal')->whereIn('no_jurnal', $noJurnal)->delete();
        DB::connection('mysql_smc')->table('posting_jurnal')->whereIn('no_jurnal', $noJurnal)->delete();
        $sik->table('rekening')->where('kd_rek', 'like', 'UJI.JP%')->delete();

        parent::tearDown();
    }

    /**
     * detailjurnal.kd_rek is a foreign key into Khanza's rekening, which the
     * structure-only sik_test ships empty.
     */
    private function rekening(): void
    {
        DB::connection('mysql_sik')->table('rekening')->insert([
            ['kd_rek' => 'UJI.JP.1', 'nm_rek' => 'Kas Uji', 'tipe' => 'N', 'balance' => 'D', 'level' => '1'],
            ['kd_rek' => 'UJI.JP.2', 'nm_rek' => 'Pendapatan Uji', 'tipe' => 'R', 'balance' => 'K', 'level' => '1'],
        ]);
    }

    /**
     * One balanced entry, staged the way a user stages it. The date is in the
     * past on purpose: Jurnal::catat() replaces today's date with now(), which
     * would make the stored time untestable.
     */
    private function staged(string $noBukti = 'BKT-UJI-100', string $jenis = 'U')
    {
        return Livewire::actingAs($this->petugasWithPermissions(['keuangan.posting-jurnal.create'], '99999901'))
            ->test(InputJurnalPosting::class)
            ->set('no_bukti', $noBukti)
            ->set('tgl_jurnal', '2026-01-15')
            ->set('jam_jurnal', '10:30:00')
            ->set('jenis', $jenis)
            ->set('keterangan', 'Setoran kas uji.')
            ->set('detail', [
                ['kd_rek' => 'UJI.JP.1', 'debet' => 150000, 'kredit' => 0],
                ['kd_rek' => 'UJI.JP.2', 'debet' => 0, 'kredit' => 150000],
            ])
            ->call('push')
            ->assertHasNoErrors();
    }

    /**
     * @test
     *
     * The running totals shown under the lines are recomputed when a line is
     * added, from the lines that were there before it.
     */
    public function adding_a_line_recomputes_the_totals_and_appends_a_blank_line(): void
    {
        Livewire::actingAs($this->petugasWithPermissions([], '99999901'))
            ->test(InputJurnalPosting::class)
            ->set('detail', [['kd_rek' => '1110', 'debet' => 70000, 'kredit' => 30000]])
            ->call('add')
            ->assertSet('totalDebet', 70000)
            ->assertSet('totalKredit', 30000)
            ->assertCount('detail', 2)
            ->assertSet('detail.1', ['kd_rek' => '', 'debet' => 0, 'kredit' => 0])
            ->assertDispatched('detailAdded');
    }

    /**
     * @test
     */
    public function staging_is_refused_without_the_permission(): void
    {
        $component = Livewire::actingAs($this->petugasWithPermissions([], '99999901'))
            ->test(InputJurnalPosting::class)
            ->set('no_bukti', 'BKT-UJI-003')
            ->set('keterangan', 'Uji tanpa izin')
            ->set('detail', [['kd_rek' => '1110', 'debet' => 1000, 'kredit' => 1000]])
            ->call('push')
            ->assertDispatched('data-denied');

        $this->assertSame([], $component->get('jurnalSementara'));
    }

    /**
     * @test
     *
     * Lines left completely blank — no account, no amounts — are dropped when
     * the entry is staged rather than carried into the posting as empty rows.
     */
    public function blank_lines_are_dropped_when_an_entry_is_staged(): void
    {
        $component = Livewire::actingAs($this->petugasWithPermissions(['keuangan.posting-jurnal.create'], '99999901'))
            ->test(InputJurnalPosting::class)
            ->set('no_bukti', 'BKT-UJI-004')
            ->set('keterangan', 'Uji baris kosong')
            ->set('detail', [
                ['kd_rek' => '1110', 'debet' => 5000, 'kredit' => 0],
                ['kd_rek' => '', 'debet' => 0, 'kredit' => 0],
                ['kd_rek' => '4110', 'debet' => 0, 'kredit' => 5000],
            ])
            ->call('push')
            ->assertHasNoErrors();

        $this->assertSame(
            ['1110', '4110'],
            array_column($component->get('jurnalSementara')[0]['detail'], 'kd_rek')
        );
    }

    /**
     * @test
     */
    public function a_staged_entry_can_be_removed_before_posting(): void
    {
        $component = $this->staged('BKT-UJI-101');

        $component->call('pop', 0);

        $this->assertSame([], $component->get('jurnalSementara'));
    }

    /**
     * @test
     *
     * The permission is checked again at posting time, not only when staging:
     * staged entries live in component state, which outlasts a revoked
     * permission.
     */
    public function posting_is_refused_without_the_permission(): void
    {
        $this->rekening();

        Livewire::actingAs($this->petugasWithPermissions([], '99999902'))
            ->test(InputJurnalPosting::class)
            ->set('jurnalSementara', [[
                'no_bukti'   => 'BKT-UJI-102',
                'tgl_jurnal' => '2026-01-15',
                'jam_jurnal' => '10:30:00',
                'jenis'      => 'U',
                'keterangan' => 'Uji tanpa izin',
                'detail'     => [
                    ['kd_rek' => 'UJI.JP.1', 'debet' => 1000, 'kredit' => 0],
                    ['kd_rek' => 'UJI.JP.2', 'debet' => 0, 'kredit' => 1000],
                ],
            ]])
            ->call('create')
            ->assertDispatched('data-denied')
            ->assertNoRedirect();

        $this->assertSame(0, Jurnal::query()->where('no_bukti', 'BKT-UJI-102')->count());
    }

    /**
     * @test
     *
     * push() accepts a line with an amount but no account; create() does not.
     * Such an entry must stop at the validator, before anything reaches Khanza.
     */
    public function posting_refuses_a_staged_line_with_no_account(): void
    {
        $this->rekening();

        Livewire::actingAs($this->petugasWithPermissions(['keuangan.posting-jurnal.create'], '99999901'))
            ->test(InputJurnalPosting::class)
            ->set('jurnalSementara', [[
                'no_bukti'   => 'BKT-UJI-103',
                'tgl_jurnal' => '2026-01-15',
                'jam_jurnal' => '10:30:00',
                'jenis'      => 'U',
                'keterangan' => 'Uji tanpa rekening',
                'detail'     => [
                    ['kd_rek' => '', 'debet' => 1000, 'kredit' => 0],
                    ['kd_rek' => 'UJI.JP.2', 'debet' => 0, 'kredit' => 1000],
                ],
            ]])
            ->call('create')
            ->assertDispatched('data-denied')
            ->assertNoRedirect();

        $this->assertSame(0, Jurnal::query()->where('no_bukti', 'BKT-UJI-103')->count());
    }

    /**
     * @test
     *
     * The full path: the jurnal header and its lines land in Khanza, SIAP
     * records that it posted them, and the user is sent to the print page for
     * exactly those jurnal.
     */
    public function posting_writes_the_jurnal_its_lines_and_the_posting_record(): void
    {
        $this->rekening();

        $this->staged('BKT-UJI-104')
            ->call('create')
            ->assertRedirect()
            ->assertSet('jurnalSementara', []);

        $jurnal = Jurnal::query()->where('no_bukti', 'BKT-UJI-104')->sole();

        $this->assertStringStartsWith('JR20260115', $jurnal->no_jurnal);
        $this->assertSame('2026-01-15', carbon($jurnal->tgl_jurnal)->toDateString());
        $this->assertSame('10:30:00', (string) $jurnal->jam_jurnal);
        $this->assertSame('SETORAN KAS UJI, DIPOSTING OLEH 99999901', $jurnal->keterangan);

        $lines = DB::connection('mysql_sik')->table('detailjurnal')->where('no_jurnal', $jurnal->no_jurnal)->get();

        $this->assertCount(2, $lines);
        $this->assertEquals(150000, $lines->sum('debet'));
        $this->assertEquals(150000, $lines->sum('kredit'));

        $this->assertTrue(
            PostingJurnal::query()->where('no_jurnal', $jurnal->no_jurnal)->exists(),
            'Jurnal tercatat di Khanza tetapi tidak tercatat di posting_jurnal SIAP.'
        );
    }

    /**
     * The form offers Umum or Penyesuaian, and the staging table labels the
     * entry accordingly. create() used to call Jurnal::catat() without its
     * $jenis argument, so every posted jurnal took the default 'U': an adjusting
     * entry was filed as a general one, and the Jurnal Posting page's jenis
     * filter showed it in the wrong list.
     *
     * @test
     */
    public function a_penyesuaian_entry_is_posted_as_penyesuaian(): void
    {
        $this->rekening();

        $this->staged('BKT-UJI-105', 'P')
            ->assertSet('jurnalSementara.0.jenis', 'P')
            ->call('create')
            ->assertRedirect();

        $this->assertSame('P', Jurnal::query()->where('no_bukti', 'BKT-UJI-105')->sole()->jenis);
    }

    /**
     * Staged entries are posted together, and each keeps its own jenis — the
     * value comes from the staged row, not from whatever the form holds when
     * "Simpan" is pressed.
     *
     * @test
     */
    public function entries_staged_with_different_jenis_each_keep_their_own(): void
    {
        $this->rekening();

        $baris = [
            ['kd_rek' => 'UJI.JP.1', 'debet' => 1000, 'kredit' => 0],
            ['kd_rek' => 'UJI.JP.2', 'debet' => 0, 'kredit' => 1000],
        ];

        $this->staged('BKT-UJI-106', 'P')
            ->set('no_bukti', 'BKT-UJI-107')
            ->set('jenis', 'U')
            ->set('keterangan', 'Jurnal umum kedua.')
            ->set('detail', $baris)
            ->call('push')
            ->assertHasNoErrors()
            // The form now says U; the first staged entry must still post as P.
            ->call('create')
            ->assertRedirect();

        $this->assertSame(
            ['BKT-UJI-106' => 'P', 'BKT-UJI-107' => 'U'],
            Jurnal::query()->whereIn('no_bukti', ['BKT-UJI-106', 'BKT-UJI-107'])->orderBy('no_bukti')->pluck('jenis', 'no_bukti')->all()
        );
    }
}
