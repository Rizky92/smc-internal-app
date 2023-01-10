<?php

namespace App\Http\Livewire\HakAkses;

use App\Models\Aplikasi\MappingAksesKhanza;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class HakAksesKhanza extends Component
{
    use WithPagination, FlashComponent;

    public $cari;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'searchData',
        'resetFilters',
        'fullRefresh',
    ];

    protected function queryString()
    {
        return [
            'cari' => ['except' => ''],
            'perpage' => ['except' => 25],
        ];
    }

    public function mount()
    {
        $this->cari = '';
        $this->perpage = 25;
    }

    public function getHakAksesKhanzaProperty()
    {
        $cari = $this->cari;

        return MappingAksesKhanza::query()
            ->where(function (Builder $query) use ($cari) {
                return $query->where('nama_field', 'like', "%{$cari}%")
                    ->orWhere('judul_menu', 'like', "%{$cari}%");
            })
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.hak-akses.hak-akses-khanza')
            ->layout(BaseLayout::class, ['title' => 'Manajemen Hak Akses SIMRS Khanza']);
    }

    public function simpan(string $field, string $judul)
    {
        if (! auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        MappingAksesKhanza::updateOrCreate(
            ['nama_field' => $field],
            ['judul_menu' => $judul]
        );

        $this->flashSuccess('Hak akses berhasil disimpan!');

        $this->searchData();
    }

    public function searchData()
    {
        $this->resetPage();

        $this->emit('$refresh');
    }

    public function resetFilters()
    {
        $this->cari = '';
        $this->perpage = 25;

        $this->searchData();
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
