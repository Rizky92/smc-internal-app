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
                            'url' => route('admin.farmasi.darurat-stok'),
                        ],
                        [
                            'name' => 'Penggunaan Obat Per Dokter',
                            'icon' => 'far fa-newspaper',
                            'url' => route('admin.farmasi.obat-perdokter'),
                        ],
                        [
                            'name' => 'Laporan Tahunan',
                            'icon' => 'far fa-newspaper',
                            'url' => route('admin.farmasi.laporan-tahunan'),
                        ],
                    ],
                ],
                [
                    'name' => 'Rekam Medis',
                    'icon' => "far fa-circle",
                    'type' => 'dropdown',
                    'items' => [
                        [
                            'name' => 'Laporan Statistik',
                            'icon' => 'far fa-newspaper',
                            'url' => route('admin.rekam-medis.laporan-statistik'),
                        ],
                    ],
                ],
                [
                    'name' => 'Logistik',
                    'icon' => "far fa-circle",
                    'type' => 'dropdown',
                    'items' => [
                        [
                            'name' => 'Input stok min max',
                            'icon' => 'fas fa-pencil-alt',
                            'url' => route('admin.logistik.minmax.index'),
                        ],
                        [
                            'name' => 'Laporan Darurat Stok',
                            'icon' => 'far fa-newspaper',
                            'url' => route('admin.logistik.darurat-stok'),
                        ],
                    ],
                ],
            ]);

            $view->with('sidebarMenu', $sidebarMenu);
        });
    }
}
