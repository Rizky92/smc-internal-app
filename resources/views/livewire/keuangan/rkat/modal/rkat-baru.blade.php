<div>
    @push('js')
        <script>
            $('#modal-anggaran-baru').on('shown.bs.modal', e => {
                @this.emit('anggaran.show-modal')
            })

            $('#modal-anggaran-baru').on('hide.bs.modal', e => {
                @this.emit('anggaran.hide-modal')
            })
        </script>
    @endpush
    @php($isUpdating = $anggaranId !== -1)
    <x-modal id="modal-anggaran-baru" :title="$isUpdating ? 'Edit Anggaran' : 'Tambah Anggaran Baru'" livewire centered>
        <x-slot name="body" class="p-0" style="overflow-x: hidden">
            <form id="form-anggaran-baru" wire:submit.prevent="{{ $anggaranId !== -1 ? 'update' : 'create' }}">
                <x-row-col class="sticky-top bg-white pt-1 pb-2 px-3">
                    <div class="form-group mt-3">
                        <label for="nama-anggaran">Nama Anggaran:</label>
                        <input type="text" id="nama-anggaran" wire:model.defer="nama" class="form-control form-control-sm" />
                    </div>
                    <div class="form-group mt-3">
                        <label for="deskripsi-anggaran">Deskripsi:</label>
                        <input type="text" id="deskripsi-anggaran" wire:model.defer="deskripsi" class="form-control form-control-sm" />
                    </div>
                </x-row-col>
            </form>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-button size="sm" class="ml-auto" data-dismiss="modal" id="batalsimpan" title="Batal" />
            <x-button size="sm" variant="primary" type="submit" class="ml-2" id="simpandata" title="Simpan" icon="fas fa-save" form="form-anggaran-baru" />
        </x-slot>
    </x-modal>
</div>
