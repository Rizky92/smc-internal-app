<?php

namespace App\Livewire\Pages\Aplikasi;

use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class Pengaturan extends Component
{
    use Concerns\PengaturanRKAT;
    use Concerns\SetNPWPPenjual;
    use FlashComponent;
    use MenuTracker;

    public function render(): View
    {
        return view('livewire.pages.aplikasi.pengaturan')
            ->layout(BaseLayout::class, ['title' => 'Pengaturan']);
    }

    public static function permissions(): string
    {
        $permissions = [];

        collect(class_uses_recursive(static::class))
            ->map(fn (string $value): string => class_basename($value))
            ->filter(fn (string $value): bool => Str::startsWith($value, 'Pengaturan'))
            ->each(function (string $value, string $key) use (&$permissions) {
                $name = 'get'.$value.'Permissions';

                $permissions = array_merge($permissions, $key::{$name}());
            });

        return implode('|', $permissions);
    }
}
