<?php

namespace App\Providers;

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
            $user = auth()->user();
            
            $sidebarMenu = collect([
                [
                    'name' => 'Dashboard',
                    'url' => route('admin.dashboard'),
                    'icon' => "fas fa-home",
                    'type' => 'link',
                    'hasAnyPermissions' => true,
                ],
                [
                    'name' => 'Farmasi',
                    'icon' => "far fa-circle",
                    'type' => 'dropdown',
                    'hasAnyPermissions' => true,
                    // 'hasAnyPermissions' => $user->can([
                    //     'farmasi.darurat-stok.read',
                    //     'farmasi.penggunaan-obat-perdokter.read',
                    //     'farmasi.laporan-tahunan.read',
                    // ]),
                    'items' => [
                        [
                            'name' => 'Laporan Darurat Stok',
                            'icon' => 'far fa-newspaper',
                            'url' => route('admin.farmasi.darurat-stok'),
                            'hasAnyPermissions' => true,
                            // 'hasAnyPermissions' => $user->can('farmasi.darurat-stok.read'),
                        ],
                        [
                            'name' => 'Penggunaan Obat Per Dokter',
                            'icon' => 'far fa-newspaper',
                            'url' => route('admin.farmasi.obat-perdokter'),
                            'hasAnyPermissions' => true,
                            // 'hasAnyPermissions' => $user->can('farmasi.penggunaan-obat-perdokter.read'),
                        ],
                        [
                            'name' => 'Laporan Tahunan',
                            'icon' => 'far fa-newspaper',
                            'url' => route('admin.farmasi.laporan-tahunan'),
                            'hasAnyPermissions' => true,
                            // 'hasAnyPermissions' => $user->can('farmasi.laporan-tahunan.read'),
                        ],
                    ],
                ],
                [
                    'name' => 'Rekam Medis',
                    'icon' => "far fa-circle",
                    'type' => 'dropdown',
                    'hasAnyPermissions' => $user->can(['rekam-medis.laporan-statistik.read']),
                    'items' => [
                        [
                            'name' => 'Laporan Statistik',
                            'icon' => 'far fa-newspaper',
                            'url' => route('admin.rekam-medis.laporan-statistik'),
                            'hasAnyPermissions' => $user->can('rekam-medis.laporan-statistik.read'),
                        ],
                    ],
                ],
                [
                    'name' => 'Logistik',
                    'icon' => "far fa-circle",
                    'type' => 'dropdown',
                    'hasAnyPermissions' => $user->can([
                        'logistik.stok-minmax.read',
                        'logistik.stok-minmax.update',
                        'logistik.darurat-stok.read',
                    ]),
                    'items' => [
                        [
                            'name' => 'Input stok min max',
                            'icon' => 'fas fa-pencil-alt',
                            'url' => route('admin.logistik.minmax.index'),
                            'hasAnyPermissions' => $user->can([
                                'logistik.stok-minmax.read',
                                'logistik.stok-minmax.update',
                            ]),
                        ],
                        [
                            'name' => 'Laporan Darurat Stok',
                            'icon' => 'far fa-newspaper',
                            'url' => route('admin.logistik.darurat-stok'),
                            'hasAnyPermissions' => $user->can('logistik.darurat-stok.read'),
                        ],
                    ],
                ],
            ]);

            $view->with('sidebarMenu', $sidebarMenu);
        });
    }
}
