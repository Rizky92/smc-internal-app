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

    public $nama;

    public $nik;

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

        $su = config('permission.superadmin_name');

        $this->title = $title;
        $this->current = URL::current();
        $this->nama = $user->nama;
        $this->nik = $user->nik;

        $this->sidebarMenu = collect([
            [
                'name' => 'Dashboard',
                'url' => route('admin.dashboard'),
                'icon' => "fas fa-home",
                'type' => 'link',
                'hasAnyPermissions' => true,
            ],
            [
                'name' => 'Perawatan',
                'icon' => 'far fa-circle',
                'type' => 'dropdown',
                'hasAnyPermissions' => $user->canAny([
                    'perawatan.daftar-pasien-ranap.read',
                    'perawatan.laporan-pasien-ranap.read',
                ]),
                'items' => [
                    [
                        'name' => 'Daftar Pasien Ranap',
                        'url' => route('admin.perawatan.daftar-pasien-ranap'),
                        'icon' => "fas fa-procedures",
                        'type' => 'link',
                        'hasAnyPermissions' => $user->can('perawatan.daftar-pasien-ranap.read'),
                    ],
                    [
                        'name' => 'Laporan Pasien Ranap',
                        'url' => route('admin.perawatan.laporan-pasien-ranap'),
                        'icon' => "fas fa-newspaper",
                        'type' => 'link',
                        'hasAnyPermissions' => $user->can('perawatan.laporan-pasien-ranap.read'),
                    ],
                ]
            ],
            [
                'name' => 'Keuangan',
                'icon' => "far fa-circle",
                'type' => 'dropdown',
                'hasAnyPermissions' => $user->canAny([
                    'keuangan.stok-obat-ruangan.read',
                    'keuangan.piutang-pasien.read',
                ]),
                'items' => [
                    [
                        'name' => 'Stok Obat Ruangan',
                        'url' => route('admin.keuangan.stok-obat-ruangan'),
                        'icon' => "fas fa-shapes",
                        'type' => 'link',
                        'hasAnyPermissions' => $user->can('keuangan.stok-obat-ruangan.read'),
                    ],
                    [
                        'name' => 'Piutang Pasien',
                        'url' => route('admin.keuangan.piutang-pasien'),
                        'icon' => "fas fa-file-invoice",
                        'type' => 'link',
                        'hasAnyPermissions' => $user->can('keuangan.piutang-pasien.read'),
                    ],
                ],
            ],
            [
                'name' => 'Farmasi',
                'icon' => "far fa-circle",
                'type' => 'dropdown',
                'hasAnyPermissions' => $user->canAny([
                    'farmasi.stok-darurat.read',
                    'farmasi.obat-per-dokter.read',
                    'farmasi.laporan-produksi.read',
                    'farmasi.kunjungan-per-bentuk-obat.read',
                    'farmasi.kunjungan-per-poli.read',
                    'farmasi.perbandingan-po-obat.read',
                ]),
                'items' => [
                    [
                        'name' => 'Stok Darurat',
                        'icon' => 'far fa-newspaper',
                        'url' => route('admin.farmasi.stok-darurat'),
                        'hasAnyPermissions' => $user->can('farmasi.stok-darurat.read'),
                    ],
                    [
                        'name' => 'Laporan Produksi',
                        'icon' => 'fas fa-th-list',
                        'url' => route('admin.farmasi.laporan-produksi'),
                        'hasAnyPermissions' => $user->can('farmasi.laporan-produksi.read'),
                    ],
                    [
                        'name' => 'Obat Per Dokter',
                        'icon' => 'fas fa-pills',
                        'url' => route('admin.farmasi.obat-per-dokter'),
                        'hasAnyPermissions' => $user->can('farmasi.obat-per-dokter.read'),
                    ],
                    [
                        'name' => 'Kunjungan Bentuk Obat',
                        'icon' => 'fas fa-pills',
                        'url' => route('admin.farmasi.kunjungan-per-bentuk-obat'),
                        'hasAnyPermissions' => $user->can('farmasi.kunjungan-per-bentuk-obat.read'),
                    ],
                    [
                        'name' => 'Kunjungan Poli',
                        'icon' => 'fas fa-pills',
                        'url' => route('admin.farmasi.kunjungan-per-poli'),
                        'hasAnyPermissions' => $user->can('farmasi.kunjungan-per-poli.read'),
                    ],
                    [
                        'name' => 'Perbandingan PO Obat',
                        'icon' => 'fas fa-balance-scale',
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
                ]),
                'items' => [
                    [
                        'name' => 'Laporan Statistik',
                        'icon' => 'fas fa-file-alt',
                        'url' => route('admin.rekam-medis.laporan-statistik'),
                        'hasAnyPermissions' => $user->can('rekam-medis.laporan-statistik.read'),
                    ],
                    [
                        'name' => 'Demografi Pasien',
                        'icon' => 'fas fa-globe-asia',
                        'url' => route('admin.rekam-medis.laporan-demografi'),
                        'hasAnyPermissions' => $user->can('rekam-medis.laporan-demografi.read'),
                    ],
                ],
            ],
            [
                'name' => 'Logistik',
                'icon' => "far fa-circle",
                'type' => 'dropdown',
                'hasAnyPermissions' => $user->canAny([
                    'logistik.input-minmax-stok.read',
                    'logistik.stok-darurat.read',
                ]),
                'items' => [
                    [
                        'name' => 'Input Minmax Stok',
                        'icon' => 'fas fa-pencil-alt',
                        'url' => route('admin.logistik.input-minmax-stok'),
                        'hasAnyPermissions' => $user->can('logistik.input-minmax-stok.read'),
                    ],
                    [
                        'name' => 'Stok Darurat',
                        'icon' => 'far fa-newspaper',
                        'url' => route('admin.logistik.stok-darurat'),
                        'hasAnyPermissions' => $user->can('logistik.stok-darurat.read'),
                    ],
                ],
            ],
            [
                'name' => 'Hak Akses',
                'icon' => "far fa-circle",
                'type' => 'dropdown',
                'hasAnyPermissions' => $user->hasRole($su),
                'items' => [
                    [
                        'name' => 'Khanza',
                        'url' => route('admin.hak-akses.khanza'),
                        'icon' => "fas fa-key",
                        'type' => 'link',
                        'hasAnyPermissions' => $user->hasRole($su),
                    ],
                    [
                        'name' => 'Custom Report',
                        'url' => route('admin.hak-akses.custom-report'),
                        'icon' => 'fas fa-key',
                        'type' => 'link',
                        'hasAnyPermissions' => $user->hasRole($su),
                    ],
                ],
            ],
            [
                'name' => 'Manajemen User',
                'url' => route('admin.manajemen-user'),
                'icon' => "fas fa-users",
                'type' => 'link',
                'hasAnyPermissions' => $user->hasRole($su),
            ],
            [
                'name' => 'Log Viewer',
                'url' => route('admin.log-viewer'),
                'icon' => "fas fa-scroll",
                'type' => 'link',
                'hasAnyPermissions' => $user->hasRole($su),
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
