<div>
    @once
        @push('js')
            <script>
                $(document).on('DOMContentLoaded', e => {
                    $('#modal-perizinan-baru').on('shown.bs.modal', e => {
                        @this.emit('siap.show-mpb')
                    })

                    $('#modal-perizinan-baru').on('hide.bs.modal', e => {
                        @this.emit('siap.hide-mpb')
                    })
                })
            </script>
        @endpush
    @endonce
    <x-modal livewire id="modal-perizinan-baru" title="Buat Role baru untuk SIAP" size="default" centered>
        <x-slot name="body">
            <form wire:submit.prevent="newRole" id="mbp-new-role">
                <x-row-col>
                    <div class="form-group">
                        <label for="role-baru">Nama role baru :</label>
                        <input type="text" wire:model.defer="namaRole" class="form-control form-control-sm" />
                    </div>
                </x-row-col>
            </form>
        </x-slot>
        <x-slot name="footer" class="justify-content-end">
            <x-button class="btn-default" title="Batal" data-dismiss="modal" />
            <x-button class="btn-primary ml-2" title="Simpan" icon="fas fa-save" data-dismiss="modal" />
        </x-slot>
    </x-modal>
</div>
