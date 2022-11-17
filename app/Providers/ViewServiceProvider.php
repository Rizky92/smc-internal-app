<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('layouts.admin', function ($view) {
            $sidebarMenu = collect([
                [
                    'name' => 'Dashboard',
                    'url' => route('admin.dashboard'),
                    'icon' => "fas fa-home",
                    'type' => 'link',
                ],
                [
                    'name' => 'Laporan',
                    'type' => 'title',
                ],
                [
                    'name' => 'Farmasi',
                    'icon' => "far fa-circle",
                    'type' => 'dropdown',
                    'items' => [
                        [
                            'name' => 'Laporan Darurat Stok',
                            'icon' => 'far fa-newspaper',
                            'url' => route('admin.farmasi.darurat-stok.index'),
                        ],
                        [
                            'name' => 'Penggunaan Obat Per Dokter',
                            'icon' => 'far fa-newspaper',
                            'url' => route('admin.farmasi.obat-perdokter.index'),
                        ],
                        [
                            'name' => 'Laporan Tahunan',
                            'icon' => 'far fa-newspaper',
                            'url' => route('admin.farmasi.laporan-tahunan.index'),
                        ],
                    ],
                ],
            ]);

            $view->with('sidebarMenu', $sidebarMenu);
        });
    }
}
