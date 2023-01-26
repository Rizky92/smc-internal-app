<?php

namespace App\Http\Livewire\HakAkses;

use App\Models\Aplikasi\MappingAksesKhanza;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

class HakAksesKhanza extends Component
{
    use WithPagination, FlashComponent, Filterable, LiveTable;

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
        return view('livewire.hak-akses.hak-akses-khanza')
            ->layout(BaseLayout::class, ['title' => 'Manajemen Hak Akses SIMRS Khanza']);
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
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
            ['judul_menu' => $judul]
        );

        tracker_end('mysql_smc');

        $this->flashSuccess('Hak akses berhasil disimpan!');

        $this->resetFilters();
    }

    public function hapusHakAkses(string $field)
    {
        if (! auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        tracker_start('mysql_smc');

        MappingAksesKhanza::destroy($field);

        tracker_end('mysql_smc');

        $this->flashSuccess('Hak akses berhasil dihapus!');

        $this->resetFilters();
    }
}
