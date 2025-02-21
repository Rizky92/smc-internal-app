<div>
    @push('js')
        <script>
            $('#modal-input-kategori-rkat').on('shown.bs.modal', e => {
                @this.emit('kategori-rkat.show-modal')
            })

            $('#modal-input-kategori-rkat').on('hide.bs.modal', e => {
                @this.emit('kategori-rkat.hide-modal')
            })

            document.addEventListener('data-saved', () => {
                $('#modal-input-kategori-rkat').modal('hide')
            })
        </script>
    @endpush

    <x-modal id="modal-input-kategori-rkat" :title="$this->isUpdating() ? 'Edit Kategori Anggaran' : 'Tambah Kategori Anggaran Baru'" livewire centered>
        <x-slot name="body" class="p-0" style="overflow-x: hidden">
            <form id="form-input-kategori-rkat" wire:submit.prevent="{{ $this->isUpdating() ? 'update' : 'create' }}">
                <x-row-col class="sticky-top bg-white pt-3 pb-1 px-3">
                    <div class="form-group">
                        <label for="nama-anggaran">Nama Anggaran:</label>
                        <input type="text" id="nama-anggaran" wire:model.defer="nama" class="form-control form-control-sm" />
                    </div>
                    <div class="form-group mt-3">
                        <label for="deskripsi-anggaran">Deskripsi:</label>
                        <textarea wire:model.defer="deskripsi" id="deskrips-anggaran" class="form-control form-control-sm"></textarea>
                    </div>
                </x-row-col>
            </form>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-button size="sm" class="ml-auto" data-dismiss="modal" id="batalsimpan" title="Batal" />
            <x-button size="sm" variant="primary" type="submit" class="ml-2" id="simpandata" title="Simpan" icon="fas fa-save" form="form-input-kategori-rkat" />
        </x-slot>
    </x-modal>
</div>
