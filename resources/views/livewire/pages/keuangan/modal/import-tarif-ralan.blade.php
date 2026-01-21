<div>
    @push('js')
        <script>
            $('#modal-import-tarif-ralan').on('shown.bs.modal', (e) => {
                Livewire.emit('tarif-ralan.show-modal');
            });

            $('#modal-import-tarif-ralan').on('hide.bs.modal', (e) => {
                Livewire.emit('tarif-ralan.hide-modal');
            });

            $(document).on('data-saved', () => {
                $('#modal-import-tarif-ralan').modal('hide');
            });
        </script>
    @endpush

    <x-modal id="modal-import-tarif-ralan" title="Import Tarif Ralan" livewire centered>
        <x-slot name="body">
            <x-form id="form-import-tarif-ralan" livewire wire:submit.prevent="importData">
                <x-form.import-file id="upload-tarif-ralan" model="fileImport" accept=".xlsx, .xls" template="templates/template-import-tarif-ralan.xlsx" />
            </x-form>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-button size="sm" class="ml-auto" data-dismiss="modal" id="batalsimpan" title="Batal" />
            <x-button
                size="sm"
                variant="primary"
                type="submit"
                class="ml-2"
                id="simpandata"
                title="Import Data"
                icon="fas fa-file-import"
                wire:target="importData"
                wire:loading.class="d-none"
                wire:loading.class.remove="d-inline-block" />
            <div wire:loading wire:target="importData" wire:loading.attr="disabled" class="ml-auto">
                <x-button size="sm" variant="primary" class="ml-2" title="Mengimpor..." icon="spinner-border spinner-border-sm" disabled />
            </div>
        </x-slot>
    </x-modal>
</div>
