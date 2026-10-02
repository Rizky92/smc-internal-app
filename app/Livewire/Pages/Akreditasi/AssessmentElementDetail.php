<?php

namespace App\Livewire\Pages\Akreditasi;

use App\Application\Akreditasi\Actions\DeleteAssessmentDocumentAction;
use App\Application\Akreditasi\Actions\GetAssessmentElementDetailAction;
use App\Application\Akreditasi\Actions\UpdateAssessmentDocumentAction;
use App\Application\Akreditasi\Actions\UploadAssessmentDocumentAction;
use App\Application\Akreditasi\DTOs\AssessmentElementDetailData;
use App\Domain\Akreditasi\Repositories\AssessmentDocumentRepositoryInterface;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class AssessmentElementDetail extends Component
{
    use DeferredLoading;
    use FlashComponent;
    use MenuTracker;
    use WithFileUploads;

    public $assessmentElementId;

    public $judulDokumen;

    public $keterangan;

    public $file;

    public $editDocumentId;

    public $editJudulDokumen;

    public $editKeterangan;

    public $deleteDocumentId;

    protected function getListeners(): array
    {
        return [
            'refreshDetail' => '$refresh',
        ];
    }

    public function getDetailProperty(): ?AssessmentElementDetailData
    {
        $action = app(GetAssessmentElementDetailAction::class);

        return $action->execute($this->assessmentElementId);
    }

    public function mount(int $assessmentElementId): void
    {
        $this->assessmentElementId = $assessmentElementId;
        $this->defaultValues();
    }

    public function render(): View
    {
        $detail = $this->detail;

        return view('livewire.pages.akreditasi.assessment-element-detail', [
            'detail' => $this->isDeferred ? null : $detail,
        ])
            ->layout(BaseLayout::class, ['title' => 'Detail EP - '.($detail->kode ?? '')]);
    }

    public function upload(): void
    {
        $this->validate([
            'file'            => 'required|file|mimes:pdf,jpg,jpeg,png,docx,xlsx|max:10240',
            'judulDokumen'    => 'required|string|max:255',
            'keterangan'      => 'nullable|string|max:500',
        ]);

        $action = app(UploadAssessmentDocumentAction::class);
        $action->execute(
            $this->assessmentElementId,
            $this->file,
            $this->judulDokumen,
            $this->keterangan,
            auth()->id() ?? '',
        );

        $this->flashSuccess('Dokumen berhasil diupload!');
        $this->resetUploadForm();
    }

    public function confirmDelete(int $documentId): void
    {
        $this->deleteDocumentId = $documentId;
    }

    public function deleteDocument(): void
    {
        if (! $this->deleteDocumentId) {
            return;
        }

        $action = app(DeleteAssessmentDocumentAction::class);
        $action->execute($this->deleteDocumentId);

        $this->flashSuccess('Dokumen berhasil dihapus!');
        $this->deleteDocumentId = null;
    }

    public function editDocument(int $documentId): void
    {
        $doc = app(AssessmentDocumentRepositoryInterface::class)->findById($documentId);

        if (! $doc) {
            return;
        }

        $this->editDocumentId = $documentId;
        $this->editJudulDokumen = $doc->judul_dokumen;
        $this->editKeterangan = $doc->keterangan;
    }

    public function updateDocument(): void
    {
        $this->validate([
            'editJudulDokumen' => 'required|string|max:255',
            'editKeterangan'   => 'nullable|string|max:500',
        ]);

        $action = app(UpdateAssessmentDocumentAction::class);
        $action->execute(
            $this->editDocumentId,
            $this->editJudulDokumen,
            $this->editKeterangan,
        );

        $this->flashSuccess('Keterangan dokumen berhasil diupdate!');
        $this->resetEditForm();
    }

    public function resetUploadForm(): void
    {
        $this->reset(['judulDokumen', 'keterangan', 'file']);
    }

    public function resetEditForm(): void
    {
        $this->reset(['editDocumentId', 'editJudulDokumen', 'editKeterangan']);
    }

    public function cancelDelete(): void
    {
        $this->deleteDocumentId = null;
    }

    protected function defaultValues(): void
    {
        //
    }
}
