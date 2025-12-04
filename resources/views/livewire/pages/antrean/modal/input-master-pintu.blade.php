<div>
    @push('js')
        <script>
            $('#modal-input-pintu').on('shown.bs.modal', (e) => {
                Livewire.emit('pintu.show-modal');
            });

            $('#modal-input-pintu').on('hide.bs.modal', (e) => {
                Livewire.emit('pintu.hide-modal');
            });

            $(document).on('data-saved', () => {
                $('#modal-input-pintu').modal('hide');
            });
        </script>
    @endpush

    <x-modal id="modal-input-pintu" :title="($this->isUpdating() ? 'Edit Data Pintu' : 'Input Data Pintu')" livewire centered>
        <x-slot name="body" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-input-pintu" livewire wire:submit.prevent="create">
                <x-row-col class="sticky-top bg-white">
                    <div class="form-group">
                        <label for="kd-pintu">Kode Pintu:</label>
                        <input type="text" id="kd-pintu" wire:model.defer="kodePintu" class="form-control form-control-sm" />
                        <x-form.error name="kodePintu" />
                    </div>
                    <div class="form-group">
                        <label for="nm-pintu">Nama Pintu:</label>
                        <input type="text" id="nm-pintu" wire:model.defer="namaPintu" class="form-control form-control-sm" />
                        <x-form.error name="namaPintu" />
                    </div>
                </x-row-col>
            </x-form>
        </x-slot>
        <x-slot name="footer">
            @if ($this->isUpdating() && user()->can('antrean.manajemen-pintu.delete'))
                <x-button size="sm" variant="danger" data-dismiss="modal" id="hapusdata" title="Hapus" icon="fas fa-trash" wire:click="delete" />
            @endif

            <x-button size="sm" class="ml-auto" data-dismiss="modal" id="batalsimpan" title="Batal" />
            <x-button
                size="sm"
                variant="primary"
                class="ml-2"
                type="submit"
                id="simpan-data"
                title="Simpan"
                icon="fas fa-save"
                form="form-input-pintu"
                wire:target="create"
                wire:loading.class="d-none"
                wire:loading.class.remove="btn" />
            <div wire:loading wire:target="create" wire:loading.attr="disabled">
                <x-button size="sm" variant="primary" class="ml-2" title="Menyimpan..." icon="spinner-border spinner-border-sm" disabled />
            </div>
        </x-slot>
    </x-modal>
</div>
