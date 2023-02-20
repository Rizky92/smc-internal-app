<?php

namespace App\Http\Livewire\HakAkses;

use App\Models\Aplikasi\MappingAksesKhanza;
use App\Models\Aplikasi\User;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

class Khanza extends Component
{
    use WithPagination, FlashComponent, Filterable, LiveTable, MenuTracker;

    public function mount()
    {
        $this->defaultValues();
    }

    public function getHakAksesKhanzaProperty()
    {
        return MappingAksesKhanza::query()
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.hak-akses.khanza')
            ->layout(BaseLayout::class, ['title' => 'Manajemen Hak Akses SIMRS Khanza']);
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
    }

    public function syncHakAkses()
    {
        if (! auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        $hakAksesUser = collect(User::rawFindByNRP('221203')->getAttributes())->except('id_user', 'password');

        $hakAksesTersedia = MappingAksesKhanza::pluck('default_value', 'nama_field');

        $hakAksesUser = $hakAksesUser->diffKeys($hakAksesTersedia);

        if ($hakAksesUser->isEmpty()) {
            $this->flashInfo('Hak akses SIMRS Khanza sudah yang terupdate!');

            return;
        }

        $hakAksesUser = $hakAksesUser->mapWithKeys(fn ($_, $field) => [$field => 'false']);

        $hakAksesBaru = $hakAksesUser
            ->map(fn ($value, $field) => ['nama_field' => $field, 'default_value' => $value])
            ->values();

        tracker_start('mysql_smc');

        MappingAksesKhanza::insert($hakAksesBaru->toArray());

        tracker_end('mysql_smc');

        tracker_start('mysql_sik');

        User::query()->update($hakAksesUser->toArray());

        tracker_end('mysql_sik');

        $this->flashSuccess('Hak akses berhasil disinkron!');

        $this->resetFilters();
    }

    public function simpanHakAkses(string $field, string $judul)
    {
        if (! auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        tracker_start('mysql_smc');

        MappingAksesKhanza::updateOrCreate(
            ['nama_field' => $field],
            ['judul_menu' => $judul, 'default_value' => 'false']
        );

        tracker_end('mysql_smc');

        $this->flashSuccess('Hak akses berhasil disimpan!');

        $this->resetFilters();
    }
}
