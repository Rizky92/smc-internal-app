<div>
    @push('js')
        <script>
            $('#modal-input-bidang-unit').on('shown.bs.modal', e => {
                @this.emit('bidang.show-modal')
            })

            $('#modal-input-bidang-unit').on('hide.bs.modal', e => {
                @this.emit('bidang.hide-modal')
            })

            $(document).on('data-saved', () => {
                $('#modal-input-bidang-unit').modal('hide')
            })
        </script>
    @endpush

    @php($isUpdating = $bidangId !== -1)
    <x-modal id="modal-input-bidang-unit" :title="$isUpdating ? 'Edit Bidang' : 'Tambah Bidang Baru'" livewire centered>
        <x-slot name="body" class="p-0" style="overflow-x: hidden">
            <form id="form-input-bidang-unit" wire:submit.prevent="{{ $bidangId !== -1 ? 'update' : 'create' }}">
                <x-row-col class="sticky-top bg-white pt-1 pb-2 px-3">
                    <div class="form-group mt-3">
                        <label for="nama-bidang">Nama Bidang:</label>
                        <input type="text" id="nama-bidang" wire:model.defer="nama" class="form-control form-control-sm" />
                    </div>
                    <div class="form-group mt-3">
                        <label for="parent-bidang">Sub-bidang dari:</label>
                        <x-form.select model="parentId" :options="$this->parentBidang" placeholder="-" placeholderValue="-1" width="full-width" />
                    </div>
                </x-row-col>
            </form>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-button size="sm" variant="danger" data-dismiss="modal" id="hapusdata" title="Hapus" icon="fas fa-trash" wire:click="delete" />
            <x-button size="sm" class="ml-auto" data-dismiss="modal" id="batalsimpan" title="Batal" />
            <x-button size="sm" variant="primary" type="submit" class="ml-2" id="simpandata" title="Simpan" icon="fas fa-save" form="form-input-bidang-unit" />
        </x-slot>
    </x-modal>
</div>
