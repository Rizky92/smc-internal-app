<div>
    @push('js')
        <script>
            $('#modal-input-kategori-rkat').on('shown.bs.modal', e => {
                // Only the "Anggaran Baru" button marks itself data-action="create".
                // Without this check, anggaranId/nama/deskripsi from a previous
                // edit stayed on the component, so reopening via Tambah right
                // after Edit kept the old title and fields and routed Simpan
                // into update() on that same row instead of creating a new one.
                var trigger = e.relatedTarget || null
                if (trigger && trigger.dataset && trigger.dataset.action === 'create') {
                    @this.dispatch('prepare', {})
                }

                @this.dispatch('kategori-rkat.show-modal')
            })

            $('#modal-input-kategori-rkat').on('hide.bs.modal', e => {
                @this.dispatch('kategori-rkat.hide-modal')
            })

            document.addEventListener('data-saved', () => {
                $('#modal-input-kategori-rkat').modal('hide')
            })
        </script>
    @endpush

    <x-modal id="modal-input-kategori-rkat" :title="$this->isUpdating() ? 'Edit Kategori Anggaran' : 'Tambah Kategori Anggaran Baru'" livewire centered>
        <x-slot name="body" class="p-0" style="overflow-x: hidden">
            <form id="form-input-kategori-rkat" wire:submit.prevent="create">
                <x-row-col class="sticky-top bg-white pt-3 pb-1 px-3">
                    <div class="form-group">
                        <label for="nama-anggaran">Nama Anggaran:</label>
                        <input type="text" id="nama-anggaran" wire:model.lazy="nama" class="form-control form-control-sm" />
                    </div>
                    <div class="form-group mt-3">
                        <label for="deskripsi-anggaran">Deskripsi:</label>
                        <textarea wire:model.lazy="deskripsi" id="deskrips-anggaran" class="form-control form-control-sm"></textarea>
                    </div>
                </x-row-col>
            </form>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-button size="sm" class="ml-auto" data-dismiss="modal" id="batalsimpan" title="Batal" />
            <x-button
                size="sm"
                variant="primary"
                type="submit"
                class="ml-2"
                id="simpandata"
                title="Simpan"
                icon="fas fa-save"
                form="form-input-kategori-rkat"
                wire:target="create"
                wire:loading.class="d-none"
                wire:loading.class.remove="btn" />
            <div wire:loading wire:target="create" wire:loading.attr="disabled">
                <x-button size="sm" variant="primary" class="ml-2" title="Menyimpan..." icon="spinner-border spinner-border-sm" disabled />
            </div>
        </x-slot>
    </x-modal>
</div>
