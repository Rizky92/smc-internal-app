<?php

namespace App\Http\Livewire\HakAkses;

use App\Models\Aplikasi\HakAkses;
use App\Models\Aplikasi\User;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Khanza extends Component
{
    use FlashComponent, Filterable, LiveTable, MenuTracker;

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getHakAksesKhanzaProperty(): Paginator
    {
        return HakAkses::query()
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.hak-akses.khanza')
            ->layout(BaseLayout::class, ['title' => 'Pengaturan perizinan SIMRS Khanza']);
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
    }

    public function syncHakAkses(): void
    {
        if (!auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        $hakAkses = collect(User::rawFindByNRP('221203')->getAttributes())->except(['id_user', 'password']);

        $hakAksesTersedia = HakAkses::pluck('default_value', 'nama_field');

        $hakAksesBaru = $hakAkses->diffKeys($hakAksesTersedia)->map(fn (?object $_): string => 'false');
        $hakAksesDibuang = $hakAksesTersedia->diffKeys($hakAkses);

        $countBaru = $hakAksesBaru->count();
        $countDibuang = $hakAksesDibuang->count();

        if ($hakAksesBaru->isEmpty() && $hakAksesDibuang->isEmpty()) {
            $this->flashInfo('Hak akses SIMRS Khanza sudah yang terupdate!');

            return;
        }

        $hakAksesUser = $hakAkses
            ->reject(fn (?string $_, string $k): bool => $hakAksesDibuang->keys()->containsStrict($k))
            ->diffKeys($hakAksesTersedia)
            ->map(fn (?string $_, string $k): array => ['nama_field' => $k, 'default_value' => 'false'])
            ->values()
            ->toArray();

        tracker_start('mysql_smc');

        HakAkses::insert($hakAksesUser);

        tracker_end('mysql_smc');

        tracker_start('mysql_sik');

        User::query()->update($hakAksesBaru->all());

        tracker_end('mysql_sik');

        $this->flashSuccess("Hak akses berhasil disinkronisasikan! Hak Akses Baru: {$countBaru} | Hak Akses Dibuang: {$countDibuang}");

        $this->resetFilters();
    }

    public function simpanHakAkses(string $field, string $judul): void
    {
        if (!auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        tracker_start('mysql_smc');

        HakAkses::updateOrCreate(
            ['nama_field' => $field],
            ['judul_menu' => $judul, 'default_value' => false]
        );

        tracker_end('mysql_smc');

        $this->flashSuccess('Hak akses berhasil disimpan!');

        $this->resetFilters();
    }
}
