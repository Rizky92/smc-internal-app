<?php

namespace App\Livewire\Pages\Mutu;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Kepegawaian\Jabatan;
use App\Models\Quality\IndikatorJabatanUnit;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class MappingJabatanUnit extends Component
{
    use DeferredLoading;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    protected $listeners = [
        'mapping-saved' => '$refresh',
    ];

    public function getCollectionProperty(): LengthAwarePaginator
    {
        return IndikatorJabatanUnit::query()
            ->with('unit')
            ->paginate($this->perpage);
    }

    public function getJabatanListProperty(): Collection
    {
        return Jabatan::query()
            ->orderBy('nm_jbtn')
            ->get()
            ->keyBy('kd_jbtn');
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.mutu.mapping-jabatan-unit', [
            'mappings' => $this->isDeferred ? [] : $this->collection,
        ])
            ->layout(BaseLayout::class, ['title' => 'Mapping Jabatan Unit']);
    }

    protected function defaultValues(): void
    {
        //
    }

    protected function dataPerSheet(): array
    {
        return [];
    }

    protected function columnHeaders(): array
    {
        return [];
    }

    protected function pageHeaders(): array
    {
        return [];
    }

    public function delete(int $id): void
    {
        IndikatorJabatanUnit::destroy($id);
        $this->flashSuccess('Mapping berhasil dihapus.');
    }
}
