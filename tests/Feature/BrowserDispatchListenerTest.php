<?php

namespace Tests\Feature;

use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Every event a Blade view dispatches from the browser has something listening
 * for it.
 *
 * A Livewire event dispatched from JavaScript that no component answers does not
 * fail. The browser sends it, nothing happens, and the feature it was meant to
 * drive silently does not run. That is exactly how three modals on Manajemen
 * User opened empty for every user after the Livewire 3 migration: the blade
 * dispatched khanza.show-sha and friends, the components listened for other
 * names, and only clicking through a real browser showed it. Livewire::test()
 * cannot see it — it calls the listener you name, not the one the blade names.
 *
 * This reads the source instead of running it: every dispatch in
 * resources/views against every #[On(...)] in app/ and every JavaScript
 * listener in resources/ and public/js. It is a static check, so it can be
 * fooled by an event name built at runtime; none are today.
 */
class BrowserDispatchListenerTest extends TestCase
{
    /**
     * Dispatches known to have no listener, with the reason each is tolerated.
     *
     * All of these are the same wiring: a modal's shown/hide handlers dispatch a
     * per-modal event, while DeferredModal listens for plain showModal and
     * hideModal. None changes what a user sees today — none of these modals reads
     * isDeferred, and each clears its form when opened, through prepare, rather
     * than when closed — but it does mean DeferredModal::hideModal() never runs
     * for any of them in the browser.
     *
     * Remove an entry once the dispatch is wired up or deleted. An entry that is
     * no longer dispatched fails the test, so the list cannot go stale.
     *
     * @return array<string, string>
     */
    private static function tolerated(): array
    {
        $alasan = 'Wiring mati: DeferredModal mendengarkan showModal/hideModal, bukan nama per-modal ini. Tanpa dampak yang terlihat (modal tidak membaca isDeferred dan dibersihkan lewat prepare saat dibuka), tetapi hideModal() tidak pernah berjalan.';

        return array_fill_keys([
            'bidang.show-modal', 'bidang.hide-modal',
            'kategori-rkat.show-modal', 'kategori-rkat.hide-modal',
            'pelaporan-rkat.show-modal', 'pelaporan-rkat.hide-modal',
            'penetapan-rkat.show-modal', 'penetapan-rkat.hide-modal',
            'pintu.show-modal', 'pintu.hide-modal',
            'posting-jurnal.show-modal', 'posting-jurnal.hide-modal',
            'tarif-lab.show-modal', 'tarif-lab.hide-modal',
            'tarif-operasi.show-modal', 'tarif-operasi.hide-modal',
            'tarif-radiologi.show-modal', 'tarif-radiologi.hide-modal',
            'tarif-ralan.show-modal', 'tarif-ralan.hide-modal',
            'tarif-ranap.show-modal', 'tarif-ranap.hide-modal',
            'utj.show', 'utj.hide',
            'siap.show', 'siap.hide',
            'siap.show-sp', 'siap.hide-sp',
            'siap.hide-la', 'siap.hide-tp',
            'khanza.hide-sha', 'khanza.hide-tha',
        ], $alasan);
    }

    /**
     * @return array<string, list<string>> event name => where it is dispatched
     */
    private function dispatchedFromViews(): array
    {
        $q = '[\'"]';
        $name = '([^\'"]+)';

        $dispatched = [];

        foreach (Finder::create()->files()->in(resource_path('views'))->name('*.blade.php') as $file) {
            $source = $file->getContents();

            $patterns = [
                // @this.dispatch('x'), $wire.dispatch('x'), Livewire.dispatch('x'), $dispatch('x'), dispatchSelf
                '/(?:@this|\$wire|Livewire|\$)\.?dispatch(?:Self)?\(\s*'.$q.$name.$q.'/',
                // dispatchTo(component, 'x') — the event is the second argument
                '/dispatchTo\(\s*'.$q.'[^\'"]+'.$q.'\s*,\s*'.$q.$name.$q.'/',
            ];

            foreach ($patterns as $pattern) {
                preg_match_all($pattern, $source, $matches, PREG_OFFSET_CAPTURE);

                foreach ($matches[1] as [$event, $offset]) {
                    $line = substr_count(substr($source, 0, $offset), "\n") + 1;
                    $dispatched[$event][] = 'resources/views/'.str_replace('\\', '/', $file->getRelativePathname()).':'.$line;
                }
            }
        }

        return $dispatched;
    }

    /**
     * @return array<string, true>
     */
    private function listenedFor(): array
    {
        $q = '[\'"]';
        $listened = [];

        foreach (Finder::create()->files()->in(app_path())->name('*.php') as $file) {
            preg_match_all('/#\[On\(\s*'.$q.'([^\'"]+)'.$q.'/', $file->getContents(), $matches);

            foreach ($matches[1] as $event) {
                $listened[$event] = true;
            }
        }

        $scripts = Finder::create()->files()
            ->in([resource_path(), public_path('js')])
            ->name(['*.php', '*.js'])
            ->notName('*.min.js');

        foreach ($scripts as $file) {
            $source = $file->getContents();

            preg_match_all('/(?:Livewire\.on|\.on|addEventListener)\(\s*'.$q.'([^\'"]+)'.$q.'/', $source, $js);
            preg_match_all('/(?:x-on:|@)([a-z0-9._-]+)\.window/i', $source, $alpine);

            foreach (array_merge($js[1], $alpine[1]) as $event) {
                $listened[$event] = true;
            }
        }

        return $listened;
    }

    /**
     * @test
     */
    public function every_event_dispatched_from_a_view_has_a_listener(): void
    {
        $dispatched = $this->dispatchedFromViews();
        $listened = $this->listenedFor();

        $this->assertNotEmpty($dispatched, 'Tidak ada dispatch yang ditemukan; pola pencariannya rusak dan test ini tidak menguji apa pun.');

        $deaf = [];

        foreach ($dispatched as $event => $where) {
            if (! isset($listened[$event]) && ! array_key_exists($event, self::tolerated())) {
                $deaf[] = "$event\n      ".implode("\n      ", array_unique($where));
            }
        }

        $this->assertSame([], $deaf, "Event berikut dikirim dari browser tetapi tidak ada komponen maupun script yang mendengarkannya, jadi fitur yang mestinya dijalankan tidak pernah jalan:\n  - ".implode("\n  - ", $deaf));
    }

    /**
     * @test
     */
    public function the_tolerated_list_only_names_dispatches_that_are_still_unheard(): void
    {
        $dispatched = $this->dispatchedFromViews();
        $listened = $this->listenedFor();

        $stale = array_values(array_filter(
            array_keys(self::tolerated()),
            fn (string $event) => ! isset($dispatched[$event]) || isset($listened[$event])
        ));

        $this->assertSame([], $stale, 'Entri berikut sudah punya pendengar atau tidak lagi dikirim; hapus dari tolerated(): '.implode(', ', $stale));
    }
}
