<?php

namespace App\Livewire\Pages\Mutu\Modal;

use App\Application\Quality\Actions\SaveQualityCategoryAction;
use App\Application\Quality\DTOs\QualityCategoryData;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Quality\QualityIndicatorCategory;
use Illuminate\View\View;
use Livewire\Component;

class InputKategoriIndikator extends Component
{
    use DeferredModal;
    use FlashComponent;

    public ?int $categoryId = null;

    /** @var string */
    public $name;

    /** @var mixed */
    protected $listeners = [
        'prepare'                             => 'loadCategory',
        'input-kategori-indikator.hide-modal' => 'hideModal',
        'input-kategori-indikator.show-modal' => 'showModal',
    ];

    public function loadCategory(?int $id = null): void
    {
        $this->resetExcept([]);

        if ($id) {
            $category = QualityIndicatorCategory::findOrFail($id);
            $this->categoryId = $category->id;
            $this->name = $category->name;
        }

        $this->isDeferred = false;
        $this->dispatchBrowserEvent('input-kategori-indikator.show-modal');
    }

    public function save(SaveQualityCategoryAction $action): void
    {
        $data = QualityCategoryData::from([
            'id'   => $this->categoryId,
            'name' => $this->name,
        ]);

        $action->execute($data);

        $this->emit('flash.success', 'Kategori Indikator berhasil disimpan.');
        $this->emit('category-saved');
        $this->hideModal();
    }

    public function hideModal(): void
    {
        $this->isDeferred = true;
        $this->dispatchBrowserEvent('input-kategori-indikator.hide-modal');
    }

    public function render(): View
    {
        return view('livewire.pages.mutu.modal.input-kategori-indikator');
    }
}
