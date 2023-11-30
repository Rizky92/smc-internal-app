<?php

namespace App\View\Components;

use App\Support\Menu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\View\Component;
use Illuminate\View\View;

class BaseLayout extends Component
{
    public ?Collection $sidebarMenu;

    public ?string $title;

    public ?string $current;

    public ?string $nama;

    public ?string $nik;

    /**
     * Create a new component instance.
     */
    public function __construct(string $title = 'Dashboard')
    {
        $user = user();

        $this->title = $title;
        $this->current = URL::current();
        $this->nama = $user->nama;
        $this->nik = $user->nik;

        $this->sidebarMenu = Menu::all($user);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('layouts.admin');
    }
}
