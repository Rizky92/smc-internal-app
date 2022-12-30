<?php

namespace App\View\Components;

use Illuminate\Support\Facades\URL;
use Illuminate\View\Component;

class BaseLayout extends Component
{
    /** @var \Illuminate\Support\Collection $sidebarMenu */
    public $sidebarMenu;

    /** @var string $title */
    public $title;

    /** @var string $current */
    public $current;

    /** @var string $username */
    public $nama;

    /** @var string $nip */
    public $nip;

    /**
     * Create a new component instance.
     * 
     * @param  string $title
     *
     * @return void
     */
    public function __construct(string $title = 'Dashboard')
    {
        /** @var \App\Models\Aplikasi\User $user */
        $user = auth()->user();

        $this->title = $title;
        $this->current = URL::current();
        $this->nama = $user->nama;
        $this->nip = $user->nip;

        $this->sidebarMenu = collect([
            [
                'name' => 'Dashboard',
                'url' => route('admin.dashboard'),
                'icon' => "fas fa-home",
                'type' => 'link',
                'hasAnyPermissions' => true,
            ],
            [
                'name' => 'Pasien Rawat Inap',
                'url' => route('admin.rawat-inap'),
                'icon' => "fas fa-hospital-alt",
                'type' => 'link',
                'hasAnyPermissions' => $user->canAny([
                    'perawatan.rawat-inap.read',
                    'perawatan.rawat-inap.batal-ranap',
                ]),
            ],
            [
                'name' => 'Keuangan',
                'icon' => "far fa-circle",
                'type' => 'dropdown',
                'hasAnyPermissions' => $user->canAny([
                    'keuangan.stok-obat-per-ruangan.read',
                ]),
                'items' => [
                    [
                        'name' => 'Stok Obat Per Ruangan',
                        'url' => route('admin.keuangan.stok-obat-per-ruangan'),
                        'icon' => "fas fa-shapes",
                        'type' => 'link',
                        'hasAnyPermissions' => $user->can('keuangan.stok-obat-per-ruangan.read'),
                    ],
                ],
            ],
            [
                'name' => 'Farmasi',
                'icon' => "far fa-circle",
                'type' => 'dropdown',
                'hasAnyPermissions' => $user->canAny([
                    'farmasi.darurat-stok.read',
                    'farmasi.penggunaan-obat-perdokter.read',
                    'farmasi.laporan-tahunan.read',
                    'farmasi.kunjungan-resep.read',
                ]),
                'items' => [
                    [
                        'name' => 'Laporan Darurat Stok',
                        'icon' => 'far fa-newspaper',
                        'url' => route('admin.farmasi.darurat-stok'),
                        'hasAnyPermissions' => $user->can('farmasi.darurat-stok.read'),
                    ],
                    [
                        'name' => 'Penggunaan Obat Per Dokter',
                        'icon' => 'far fa-newspaper',
                        'url' => route('admin.farmasi.obat-perdokter'),
                        'hasAnyPermissions' => $user->can('farmasi.penggunaan-obat-perdokter.read'),
                    ],
                    [
                        'name' => 'Kunjungan Resep',
                        'icon' => 'far fa-newspaper',
                        'url' => route('admin.farmasi.kunjungan-resep'),
                        'hasAnyPermissions' => $user->can('farmasi.kunjungan-resep.read'),
                    ],
                    [
                        'name' => 'Kunjungan Farmasi Pasien',
                        'icon' => 'far fa-newspaper',
                        'url' => route('admin.farmasi.kunjungan-pasien-per-poli'),
                        'hasAnyPermissions' => $user->can('farmasi.kunjungan-pasien-per-poli.read'),
                    ],
                    [
                        'name' => 'Laporan Tahunan',
                        'icon' => 'far fa-newspaper',
                        'url' => route('admin.farmasi.laporan-tahunan'),
                        'hasAnyPermissions' => $user->can('farmasi.laporan-tahunan.read'),
                    ],
                    [
                        'name' => 'Ringkasan Perbandingan PO Obat',
                        'icon' => 'far fa-newspaper',
                        'url' => route('admin.farmasi.perbandingan-po-obat'),
                        'hasAnyPermissions' => $user->can('farmasi.perbandingan-po-obat.read'),
                    ],
                ],
            ],
            [
                'name' => 'Rekam Medis',
                'icon' => "far fa-circle",
                'type' => 'dropdown',
                'hasAnyPermissions' => $user->canAny([
                    'rekam-medis.laporan-statistik.read',
                    'rekam-medis.demografi-pasien.read',
                ]),
                'items' => [
                    [
                        'name' => 'Laporan Statistik',
                        'icon' => 'far fa-newspaper',
                        'url' => route('admin.rekam-medis.laporan-statistik'),
                        'hasAnyPermissions' => $user->can('rekam-medis.laporan-statistik.read'),
                    ],
                    [
                        'name' => 'Demografi Pasien',
                        'icon' => 'far fa-newspaper',
                        'url' => route('admin.rekam-medis.demografi-pasien'),
                        'hasAnyPermissions' => $user->can('rekam-medis.demografi-pasien.read'),
                    ],
                ],
            ],
            [
                'name' => 'Logistik',
                'icon' => "far fa-circle",
                'type' => 'dropdown',
                'hasAnyPermissions' => $user->canAny([
                    'logistik.stok-minmax.read',
                    'logistik.stok-minmax.update',
                    'logistik.darurat-stok.read',
                ]),
                'items' => [
                    [
                        'name' => 'Input stok min max',
                        'icon' => 'fas fa-pencil-alt',
                        'url' => route('admin.logistik.minmax'),
                        'hasAnyPermissions' => $user->canAny([
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
            [
                'name' => 'Admin',
                'icon' => "fas fa-users-cog",
                'type' => 'dropdown',
                'hasAnyPermissions' => $user->hasRole(config('permission.superadmin_name')),
                'items' => [
                    [
                        'name' => 'Manajemen User',
                        'url' => route('admin.users.manajemen'),
                        'icon' => "fas fa-users",
                        'type' => 'link',
                        'hasAnyPermissions' => $user->hasRole(config('permission.superadmin_name')),
                    ],
                    [
                        'name' => 'Pengaturan Hak Akses',
                        'url' => route('admin.users.hak-akses'),
                        'icon' => "fas fa-key",
                        'type' => 'link',
                        'hasAnyPermissions' => $user->hasRole(config('permission.superadmin_name')),
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('layouts.admin');
    }
}
