<?php

namespace App\Livewire\Pages\HakAkses;

use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Aplikasi\HakAkses;
use App\Models\Aplikasi\User;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;

class Khanza extends Component
{
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @psalm-return LengthAwarePaginator
     */
    public function getHakAksesKhanzaProperty(): Paginator
    {
        return HakAkses::query()
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.hak-akses.khanza')
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
        if (! user()->hasRole(config('permission.superadmin_name'))) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        $hakAkses = collect(User::rawFindByNRP('221203')->getAttributes())->except(['id_user', 'password']);

        $hakAksesTersedia = HakAkses::pluck('default_value', 'nama_field');

        $hakAksesBaru = $hakAkses->diffKeys($hakAksesTersedia)->map(fn ($_): string => 'false');
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

        User::unguard();

        User::query()
            ->withoutGlobalScopes()
            ->update($hakAksesBaru->all());

        User::reguard();

        tracker_end('mysql_sik');

        $this->flashSuccess("Hak akses berhasil disinkronisasikan! Hak Akses Baru: {$countBaru} | Hak Akses Dibuang: {$countDibuang}");

        $this->resetFilters();
    }

    public function simpanHakAkses(string $field, string $judul): void
    {
        if (! user()->hasRole(config('permission.superadmin_name'))) {
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
