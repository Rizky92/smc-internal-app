<div>
    @push('js')
        <script>
            $('#modal-import-tarif-lab').on('shown.bs.modal', (e) => {
                Livewire.dispatch('tarif-lab.show-modal');
            });

            $('#modal-import-tarif-lab').on('hide.bs.modal', (e) => {
                Livewire.dispatch('tarif-lab.hide-modal');
            });

            $(document).on('data-saved', () => {
                $('#modal-import-tarif-lab').modal('hide');
            });
        </script>
    @endpush

    <x-modal id="modal-import-tarif-lab" title="Import Tarif Laboratorium" livewire centered>
        <x-slot name="body">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-import-tarif-lab" livewire wire:submit.prevent="importData">
                <x-form.import-file id="upload-tarif-lab" model="fileImport" accept=".xlsx, .xls" template="templates/template-import-tarif-lab.xlsx" />
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
                form="form-import-tarif-lab"
                wire:target="importData"
                wire:loading.class="d-none"
                wire:loading.class.remove="btn" />
            <div wire:loading wire:target="importData" wire:loading.attr="disabled">
                <x-button size="sm" variant="primary" class="ml-2" title="Mengimpor..." icon="spinner-border spinner-border-sm" disabled />
            </div>
        </x-slot>
    </x-modal>
</div>
