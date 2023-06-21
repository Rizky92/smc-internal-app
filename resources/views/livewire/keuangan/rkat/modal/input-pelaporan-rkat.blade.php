<div>
    @push('js')
        <script>
            $('#modal-input-pelaporan-rkat').on('shown.bs.modal', e => {
                @this.emit('pelaporan-rkat.show-modal')
            })

            $('#modal-input-pelaporan-rkat').on('hide.bs.modal', e => {
                @this.emit('pelaporan-rkat.hide-modal')
            })
        </script>
    @endpush
    @php($isUpdating = $anggaranId !== -1)
    <x-modal id="modal-input-pelaporan-rkat" :title="$isUpdating ? 'Edit Kategori Anggaran' : 'Tambah Kategori Anggaran Baru'" livewire centered>
        <x-slot name="body" class="p-0" style="overflow-x: hidden">
            <form id="form-input-pelaporan-rkat" wire:submit.prevent="{{ $anggaranId !== -1 ? 'update' : 'create' }}">
                <x-row-col class="sticky-top bg-white pt-1 pb-2 px-3">
                    <div class="form-group mt-3">
                        <label for="nama-anggaran">Nama Anggaran:</label>
                        <input type="text" id="nama-anggaran" wire:model.defer="nama" class="form-control form-control-sm" />
                    </div>
                </x-row-col>
            </form>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-button size="sm" class="ml-auto" data-dismiss="modal" id="batalsimpan" title="Batal" />
            <x-button size="sm" variant="primary" type="submit" class="ml-2" id="simpandata" title="Simpan" icon="fas fa-save" form="form-input-pelaporan-rkat" />
        </x-slot>
    </x-modal>
</div>
