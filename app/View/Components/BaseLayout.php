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
                'name' => 'Daftar Pasien Ranap',
                'url' => route('admin.rawat-inap'),
                'icon' => "fas fa-hospital-alt",
                'type' => 'link',
                'hasAnyPermissions' => $user->canAny([
                    'perawatan.rawat-inap.read',
                    'perawatan.daftar-pasien-ranap.read',
                ]),
            ],
            [
                'name' => 'Keuangan',
                'icon' => "far fa-circle",
                'type' => 'dropdown',
                'hasAnyPermissions' => $user->canAny([
                    'keuangan.stok-obat-per-ruangan.read',
                    'keuangan.stok-obat-ruangan.read',
                ]),
                'items' => [
                    [
                        'name' => 'Stok Obat Ruangan',
                        'url' => route('admin.keuangan.stok-obat-per-ruangan'),
                        'icon' => "fas fa-shapes",
                        'type' => 'link',
                        'hasAnyPermissions' => $user->canAny([
                            'keuangan.stok-obat-per-ruangan.read',
                            'keuangan.stok-obat-ruangan.read',
                        ]),
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

                    'farmasi.stok-darurat.read',
                    'farmasi.obat-per-dokter.read',
                    'farmasi.laporan-produksi.read',
                    'farmasi.kunjungan-per-bentuk-obat.read',
                    'farmasi.kunjungan-per-poli.read',
                    'farmasi.perbandingan-po-obat.read',
                ]),
                'items' => [
                    [
                        'name' => 'Darurat Stok',
                        'icon' => 'far fa-newspaper',
                        'url' => route('admin.farmasi.darurat-stok'),
                        'hasAnyPermissions' => $user->canAny([
                            'farmasi.darurat-stok.read',
                            'farmasi.stok-darurat.read',
                        ]),
                    ],
                    [
                        'name' => 'Obat Per Dokter',
                        'icon' => 'far fa-newspaper',
                        'url' => route('admin.farmasi.obat-perdokter'),
                        'hasAnyPermissions' => $user->canAny([
                            'farmasi.penggunaan-obat-perdokter.read',
                            'farmasi.obat-per-dokter.read',
                        ]),
                    ],
                    [
                        'name' => 'Kunjungan Per Bentuk Obat',
                        'icon' => 'far fa-newspaper',
                        'url' => route('admin.farmasi.kunjungan-resep'),
                        'hasAnyPermissions' => $user->canAny([
                            'farmasi.kunjungan-resep.read',
                            'farmasi.kunjungan-per-bentuk-obat.read',
                        ]),
                    ],
                    [
                        'name' => 'Kunjungan Per Poli',
                        'icon' => 'far fa-newspaper',
                        'url' => route('admin.farmasi.kunjungan-pasien-per-poli'),
                        'hasAnyPermissions' => $user->canAny([
                            'farmasi.kunjungan-pasien-per-poli.read',
                            'farmasi.kunjungan-per-poli.read',
                        ]),
                    ],
                    [
                        'name' => 'Laporan Produksi',
                        'icon' => 'far fa-newspaper',
                        'url' => route('admin.farmasi.laporan-tahunan'),
                        'hasAnyPermissions' => $user->canAny([
                            'farmasi.laporan-tahunan.read',
                            'farmasi.laporan-produksi.read',
                        ]),
                    ],
                    [
                        'name' => 'Perbandingan PO Obat',
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
                    'rekam-medis.laporan-demografi.read',
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
                        'hasAnyPermissions' => $user->canAny([
                            'rekam-medis.demografi-pasien.read',
                            'rekam-medis.laporan-demografi.read',
                        ]),
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

                    'logistik.input-minmax-stok.read',
                    'logistik.stok-darurat.read',
                ]),
                'items' => [
                    [
                        'name' => 'Input Stok Minmax',
                        'icon' => 'fas fa-pencil-alt',
                        'url' => route('admin.logistik.minmax'),
                        'hasAnyPermissions' => $user->canAny([
                            'logistik.stok-minmax.read',
                            'logistik.stok-minmax.update',

                            'logistik.input-minmax-stok.create',
                            'logistik.input-minmax-stok.read',
                            'logistik.input-minmax-stok.update',
                        ]),
                    ],
                    [
                        'name' => 'Darurat Stok',
                        'icon' => 'far fa-newspaper',
                        'url' => route('admin.logistik.darurat-stok'),
                        'hasAnyPermissions' => $user->canAny([
                            'logistik.darurat-stok.read',
                            'logistik.stok-darurat.read',
                        ]),
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
