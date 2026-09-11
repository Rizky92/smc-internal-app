<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Components\DatabaseNotification;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The notification bell in the header, present on every page.
 *
 * download() used to flash a missing-file warning with $this->emit(), removed
 * in Livewire 3 - calling it was a fatal error, on a component every
 * authenticated page renders.
 */
class DatabaseNotificationTest extends TestCase
{
    /**
     * @test
     */
    public function flashes_an_error_instead_of_fataling_when_the_file_is_missing(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        Livewire::actingAs($petugas)
            ->test(DatabaseNotification::class)
            ->call('download', 'no-such-file.pdf')
            ->assertDispatched('flash.error');
    }
}
