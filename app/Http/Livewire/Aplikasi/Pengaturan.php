<?php

namespace App\Http\Livewire\Aplikasi;

use App\Support\Livewire\Concerns\FlashComponent;
use App\Support\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class Pengaturan extends Component
{
    use FlashComponent, MenuTracker;

    // Pengaturan per kategori aplikasi masuk jadi Traits
    use Concerns\PengaturanRKAT;

    public function render(): View
    {
        return view('livewire.aplikasi.pengaturan')
            ->layout(BaseLayout::class, ['title' => 'Pengaturan']);
    }
}
