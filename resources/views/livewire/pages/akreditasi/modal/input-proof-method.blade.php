<div>
    @push('js')
        <script>
            window.addEventListener('input-proof-method.show-modal', (e) => {
                $('#modal-input-proof-method').modal('show');
            });

            window.addEventListener('input-proof-method.hide-modal', (e) => {
                $('#modal-input-proof-method').modal('hide');
            });

            $(document).on('proof-method-saved', () => {
                $('#modal-input-proof-method').modal('hide');
            });
        </script>
    @endpush

    <x-modal id="modal-input-proof-method" title="Input Metode Pembuktian" size="lg" livewire>
        <x-slot name="body" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-input-proof-method" wire:submit.prevent="save">
                <x-row>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Kode</label>
                            <input type="text" wire:model.defer="kode" class="form-control form-control-sm" placeholder="Mis: PK" />
                            <x-form.error name="kode" />
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Nama Metode Pembuktian</label>
                            <input type="text" wire:model.defer="nama" class="form-control form-control-sm" placeholder="Nama lengkap metode" />
                            <x-form.error name="nama" />
                        </div>
                    </div>
                </x-row>
            </x-form>
        </x-slot>
        <x-slot name="footer">
            <x-button variant="primary" form="form-input-proof-method" type="submit" wire:loading.attr="disabled" title="Simpan" />
        </x-slot>
    </x-modal>
</div>
