<?php

namespace App\Http\Livewire\Aplikasi;

use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class Pengaturan extends Component
{
    use FlashComponent, MenuTracker;

    // Pengaturan per kategori aplikasi masuk jadi Traits
    use Concerns\PengaturanRKAT;

    public function mount(): void
    {
        //
    }

    public function render(): View
    {
        return view('livewire.aplikasi.pengaturan')
            ->layout(BaseLayout::class, ['title' => 'Pengaturan']);
    }
}
