<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * wire:poll="call" looks like it invokes the component's call() method. It
 * doesn't: Livewire 3's $wire proxy reserves "call" as an alias for its own
 * $call() helper (alongside on, el, id, js, get, set, hook, commit, watch,
 * entangle, dispatch, dispatchTo, dispatchSelf, upload, uploadMultiple,
 * removeUpload, cancelUpload — see vendor/livewire/livewire/dist/livewire.js,
 * the `aliases` map in js/$wire.js). The bare-shorthand form used by
 * wire:poll/wire:click/etc — action names given as a property, not a call —
 * resolves through that alias table first, so any of these names silently
 * calls Livewire's own helper with no arguments instead of the component's
 * method. It fails at runtime, in the browser, with the server logging
 * "Public method [undefined] not found" — nothing before that points at
 * which directive caused it. AntreanDiPanggil::call() was exactly this: a
 * queue-calling display board that 500'd on every wire:poll tick.
 *
 * This can't be caught by Livewire::test() — that dispatches through PHP
 * directly, bypassing the JS $wire proxy entirely. It has to be caught
 * statically, by checking the actual name used in a wire:poll="name" or
 * wire:click="name" style expression.
 */
class WireShorthandReservedWordsTest extends TestCase
{
    private const RESERVED = [
        'on', 'el', 'id', 'js', 'get', 'set', 'call', 'hook', 'commit', 'watch',
        'entangle', 'dispatch', 'dispatchTo', 'dispatchSelf',
        'upload', 'uploadMultiple', 'removeUpload', 'cancelUpload',
    ];

    /**
     * @test
     */
    public function no_blade_view_calls_a_reserved_wire_alias_by_bare_shorthand(): void
    {
        $pattern = '/wire:(?:poll|click|submit|change|keydown|keyup|keypress'
            .'|mouseenter|mouseleave|blur|focus|input|dblclick|mousedown|mouseup)'
            .'[a-zA-Z0-9.\-]*\s*=\s*[\'"]('.implode('|', self::RESERVED).')(\(\))?[\'"]/';

        $offenders = [];

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $lines = file($file->getPathname());

            foreach ($lines as $i => $line) {
                if (preg_match($pattern, $line, $m)) {
                    $offenders[] = str_replace(resource_path('views').DIRECTORY_SEPARATOR, '', $file->getPathname())
                        .':'.($i + 1).' uses reserved $wire alias "'.$m[1].'"';
                }
            }
        }

        $this->assertSame([], $offenders, implode("\n", $offenders));
    }
}
