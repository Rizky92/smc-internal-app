<?php

namespace Tests\Feature\Livewire\Concerns;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Exceptions\EventHandlerDoesNotExist;
use Livewire\Livewire;
use ReflectionClass;
use ReflectionMethod;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Tests\Fixtures\Livewire\ModalHarness;
use Tests\Fixtures\Livewire\PlainModalHarness;
use Tests\Fixtures\Livewire\RenamedEventModalHarness;
use Tests\TestCase;

/**
 * DeferredModal, inherited by 19 page components.
 *
 * A modal in SIAP is not mounted when it opens — it is already on the page,
 * deferred, and the open gesture only lifts the deferral. That makes closing it
 * the dangerous half: whatever the last row put into the component is still
 * there when the next row opens it, unless hideModal clears it.
 */
class DeferredModalTest extends TestCase
{
    /**
     * @test
     */
    public function show_modal_mengangkat_deferral_dan_mengumumkan_modal_loaded(): void
    {
        Livewire::actingAs($this->petugasWithPermissions())
            ->test(ModalHarness::class)
            ->assertSet('isDeferred', true)
            ->dispatch('showModal')
            ->assertSet('isDeferred', false)
            ->assertDispatched('modal-loaded');
    }

    /**
     * @test
     */
    public function hide_modal_mengumumkan_modal_unloaded(): void
    {
        Livewire::actingAs($this->petugasWithPermissions())
            ->test(ModalHarness::class)
            ->dispatch('showModal')
            ->dispatch('hideModal')
            ->assertDispatched('modal-unloaded');
    }

    /**
     * A modal without Filterable goes back to deferred when it closes, which is
     * what undefer() is there for.
     *
     * @test
     */
    public function modal_tanpa_filterable_kembali_deferred_setelah_ditutup(): void
    {
        Livewire::actingAs($this->petugasWithPermissions())
            ->test(PlainModalHarness::class)
            ->dispatch('showModal')
            ->assertSet('isDeferred', false)
            ->dispatch('hideModal')
            ->assertSet('isDeferred', true);
    }

    /**
     * A modal that also filters has to end up deferred too, and that is the
     * ordering constraint inside hideModal(): resetFilters() runs through
     * Filterable::searchData(), which lifts the deferral, so re-deferring has to
     * be the last thing it does.
     *
     * It used to be the first, which left all 11 components using both traits
     * permanently loaded after the modal had been closed once — the parent page
     * then re-ran the modal's Khanza query on every render. Swapping the two
     * statements is the whole fix; this test is what holds the order in place.
     *
     * @test
     */
    public function menutup_modal_ber_filter_tetap_mengembalikan_deferral(): void
    {
        Livewire::actingAs($this->petugasWithPermissions())
            ->test(ModalHarness::class)
            ->dispatch('showModal')
            ->assertSet('isDeferred', false)
            ->dispatch('hideModal')
            ->assertSet('isDeferred', true);
    }

    /**
     * The reason hideModal exists. Without the resetFilters() call, a modal
     * opened for the next row still shows the previous row's state, because the
     * component was never torn down.
     *
     * @test
     */
    public function menutup_modal_membuang_state_baris_sebelumnya(): void
    {
        Livewire::actingAs($this->petugasWithPermissions())
            ->test(ModalHarness::class)
            ->dispatch('showModal')
            ->set('namaBidang', 'Keuangan')
            ->set('cari', 'lama')
            ->dispatch('hideModal')
            ->assertSet('namaBidang', '')
            ->assertSet('cari', '');
    }

    /**
     * The other side of hideModal's method_exists() guard: a modal that does not
     * filter has nothing to reset, and closing it must not fail looking for the
     * method.
     *
     * @test
     */
    public function modal_tanpa_filterable_tetap_bisa_ditutup(): void
    {
        Livewire::actingAs($this->petugasWithPermissions())
            ->test(PlainModalHarness::class)
            ->dispatch('showModal')
            ->dispatch('hideModal')
            ->assertOk()
            ->assertDispatched('modal-unloaded');
    }

    /**
     * @test
     */
    public function override_show_modal_menjawab_nama_event_barunya(): void
    {
        Livewire::actingAs($this->petugasWithPermissions())
            ->test(RenamedEventModalHarness::class)
            ->dispatch('harness.show-custom')
            ->assertSet('isDeferred', false)
            ->assertSet('dimuat', true)
            ->assertDispatched('modal-loaded');
    }

    /**
     * And no longer answers to the trait's — Livewire does not fall back, it
     * refuses outright. This is how PHP attributes work, the override replaces
     * the method the trait's #[On] was attached to, but it is also the exact
     * mechanism that silently disabled three modals on the Manajemen User page
     * during the Livewire 3 migration. The exception below is what the blade's
     * dispatch got instead of an open modal.
     *
     * @test
     */
    public function override_show_modal_tidak_lagi_menjawab_event_bawaan_trait(): void
    {
        $this->expectException(EventHandlerDoesNotExist::class);

        Livewire::actingAs($this->petugasWithPermissions())
            ->test(RenamedEventModalHarness::class)
            ->dispatch('showModal');
    }

    /**
     * The sweep that generalises the case above across the application: any
     * component overriding showModal() or hideModal() has dropped the trait's
     * listener and must declare a replacement, or the modal is unreachable from
     * the blade that opens it.
     *
     * Driven off the filesystem rather than a list, so a component added later
     * is covered without anyone remembering to add it here.
     *
     * @test
     */
    public function setiap_override_modal_membawa_listener_on_nya_sendiri(): void
    {
        $tanpaListener = [];

        foreach ($this->livewireComponentClasses() as $class) {
            foreach (['showModal', 'hideModal'] as $nama) {
                $reflection = new ReflectionClass($class);

                if (! $reflection->hasMethod($nama)) {
                    continue;
                }

                $method = $reflection->getMethod($nama);

                // Declared by the trait rather than the component: the trait's
                // own attribute still applies.
                if ($method->getDeclaringClass()->getName() !== $class) {
                    continue;
                }

                if ($this->listenerEvents($method) === []) {
                    $tanpaListener[] = $class.'::'.$nama.'()';
                }
            }
        }

        $this->assertSame([], $tanpaListener, sprintf(
            "Override berikut menimpa listener milik DeferredModal tanpa mendeklarasikan #[On(...)] pengganti, sehingga modalnya tidak bisa dibuka dari blade:\n  - %s",
            implode("\n  - ", $tanpaListener)
        ));
    }

    /**
     * @return list<string>
     */
    private function livewireComponentClasses(): array
    {
        $classes = [];

        /** @var SplFileInfo $file */
        foreach (Finder::create()->files()->in(app_path('Livewire'))->name('*.php') as $file) {
            $relative = str($file->getRealPath())
                ->replace([app_path(), DIRECTORY_SEPARATOR], ['App', '\\'])
                ->beforeLast('.php')
                ->value();

            if (! class_exists($relative)) {
                continue;
            }

            $reflection = new ReflectionClass($relative);

            if ($reflection->isAbstract() || ! $reflection->isSubclassOf(Component::class)) {
                continue;
            }

            $classes[] = $relative;
        }

        $this->assertNotEmpty($classes, 'Tidak ada komponen Livewire yang ditemukan; sapuan ini tidak menguji apa pun.');

        return $classes;
    }

    /**
     * @return list<string>
     */
    private function listenerEvents(ReflectionMethod $method): array
    {
        return array_map(
            fn ($attribute) => $attribute->newInstance()->event,
            $method->getAttributes(On::class)
        );
    }
}
