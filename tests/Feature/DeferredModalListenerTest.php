<?php

namespace Tests\Feature;

use Livewire\Attributes\On;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

/**
 * DeferredModal declares #[On('showModal')] on its own showModal(), but PHP
 * attributes are not inherited by an override: a component that redeclares
 * showModal() to listen for its own event name silently drops the trait's
 * registration, and gains nothing unless it restates #[On(...)] itself.
 *
 * Nothing in this app ever dispatches the bare 'showModal'/'hideModal' name -
 * every modal uses a unique one so the five modals hosted side by side on
 * manajemen-user.blade.php do not all answer the same event. So the trait's
 * own listener is dead weight, and there is no fallback when an override
 * forgets the attribute: the modal opens, isDeferred is never lifted, and the
 * list inside renders empty for every record, with no error anywhere.
 *
 * That exact mistake shipped four times - SetHakAkses, TransferHakAkses,
 * TransferPerizinan and LihatAktivitas - and Livewire::test() cannot catch it,
 * because calling showModal()/loadProperties() by name works fine whether or
 * not the event is wired. This pins the invariant statically instead.
 */
class DeferredModalListenerTest extends TestCase
{
    private const OVERRIDABLE = ['showModal', 'hideModal'];

    /**
     * @return list<class-string>
     */
    private function livewireComponents(): array
    {
        $classes = [];

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(app_path('Livewire'), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if (! str_ends_with($file->getFilename(), '.php')) {
                continue;
            }

            $relative = str_replace(app_path('Livewire').DIRECTORY_SEPARATOR, '', $file->getPathname());
            $class = 'App\\Livewire\\'.str_replace([DIRECTORY_SEPARATOR, '.php'], ['\\', ''], $relative);

            if (class_exists($class) && ! (new ReflectionClass($class))->isAbstract()) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    /**
     * @test
     */
    public function every_overridden_modal_hook_restates_its_own_listener(): void
    {
        $offenders = [];

        foreach ($this->livewireComponents() as $class) {
            foreach (self::OVERRIDABLE as $hook) {
                if (! method_exists($class, $hook)) {
                    continue;
                }

                $method = new ReflectionMethod($class, $hook);

                // Declared by the trait rather than the component: the trait's
                // own attribute still applies, nothing to restate.
                if ($method->getDeclaringClass()->getName() !== $class) {
                    continue;
                }

                if ($method->getAttributes(On::class) === []) {
                    $offenders[] = $class.'::'.$hook.'() overrides the trait without an #[On(...)] attribute';
                }
            }
        }

        $this->assertSame([], $offenders, implode("\n", $offenders));
    }
}
