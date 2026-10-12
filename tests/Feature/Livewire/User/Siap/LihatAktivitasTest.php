<?php

namespace Tests\Feature\Livewire\User\Siap;

use App\Livewire\Pages\User\Siap\LihatAktivitas;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the read-only activity log opened alongside ManajemenUser's other
 * four modals.
 *
 * getAktivitasUserProperty() is gated on both isDeferred (only lifted by
 * DeferredModal's showModal(), fired when the modal actually opens - not by
 * prepareUser(), which only fills userId/nama) and an empty userId. Both
 * conditions have to be satisfied before any query runs at all.
 */
class LihatAktivitasTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::connection('mysql_smc')->table('trackermenu')->where('user_id', 'like', '9999990%')->delete();

        parent::tearDown();
    }

    private function aktivitas(string $userId, string $waktu, string $routeName): void
    {
        DB::connection('mysql_smc')->table('trackermenu')->insert([
            'waktu'       => $waktu,
            'breadcrumbs' => 'Uji > '.$routeName,
            'route_name'  => $routeName,
            'user_id'     => $userId,
            'ip_address'  => '127.0.0.1',
        ]);
    }

    /**
     * @test
     */
    public function preparing_a_user_fills_the_form(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        Livewire::actingAs($petugas)
            ->test(LihatAktivitas::class)
            ->dispatch('siap.prepare-la', userId: '99999902', nama: 'Petugas Uji')
            ->assertSet('userId', '99999902')
            ->assertSet('nama', 'Petugas Uji');
    }

    /**
     * @test
     *
     * Before the modal is actually shown (isDeferred still true) the
     * activity query never runs, regardless of whether userId is set.
     */
    public function shows_no_activity_before_the_modal_is_loaded(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $this->aktivitas('99999902', '2026-03-05 08:00:00', 'admin.dashboard');

        $aktivitas = Livewire::actingAs($petugas)
            ->test(LihatAktivitas::class)
            ->set('userId', '99999902')
            ->get('aktivitasUser');

        $this->assertSame([], $aktivitas);
    }

    /**
     * @test
     *
     * groupBy() buckets each row under its own calendar date, so two
     * activities logged on different days end up as two separate groups
     * rather than one flat list.
     */
    public function groups_activity_by_date_once_loaded(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $this->aktivitas('99999902', '2026-03-05 08:00:00', 'admin.dashboard');
        $this->aktivitas('99999902', '2026-03-05 09:30:00', 'admin.keuangan.buku-besar');
        $this->aktivitas('99999902', '2026-03-06 10:00:00', 'admin.antrean');

        $aktivitas = Livewire::actingAs($petugas)
            ->test(LihatAktivitas::class)
            ->call('loadProperties')
            ->set('userId', '99999902')
            ->get('aktivitasUser');

        $this->assertCount(2, $aktivitas);
        $this->assertCount(2, $aktivitas->get('2026-03-05'));
        $this->assertCount(1, $aktivitas->get('2026-03-06'));
    }

    /**
     * @test
     *
     * The test above lifts isDeferred by calling loadProperties() by name,
     * which is not how the modal does it: lihat-aktivitas.blade.php fires
     * 'siap.show-la' from jQuery's shown.bs.modal. DeferredModal only
     * registers #[On('showModal')], and nothing in this app dispatches that
     * generic name - so without a listener for 'siap.show-la' the modal opens
     * with isDeferred still true and the timeline is empty for every user.
     * Calling the method directly can never catch that; dispatching the event
     * the blade actually sends can.
     */
    public function loads_activity_when_the_modal_fires_its_own_shown_event(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $this->aktivitas('99999902', '2026-03-05 08:00:00', 'admin.dashboard');

        $aktivitas = Livewire::actingAs($petugas)
            ->test(LihatAktivitas::class)
            ->set('userId', '99999902')
            ->dispatch('siap.show-la')
            ->get('aktivitasUser');

        $this->assertCount(1, $aktivitas);
    }
}
