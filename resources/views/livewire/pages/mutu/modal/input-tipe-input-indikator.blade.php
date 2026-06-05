<div>
    @push('js')
        <script>
            window.addEventListener('input-tipe-input-indikator.show-modal', (e) => {
                $('#modal-input-tipe-input-indikator').modal('show');
            });

            window.addEventListener('input-tipe-input-indikator.hide-modal', (e) => {
                $('#modal-input-tipe-input-indikator').modal('hide');
            });

            $(document).on('input-type-saved', () => {
                $('#modal-input-tipe-input-indikator').modal('hide');
            });
        </script>
    @endpush

    <x-modal id="modal-input-tipe-input-indikator" title="Input Tipe Input Indikator" size="lg" livewire>
        <x-slot name="body" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-input-tipe-input-indikator" wire:submit.prevent="save">
                <x-row>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Nama Tipe Input</label>
                            <input type="text" wire:model.defer="name" class="form-control form-control-sm" placeholder="Masukan Nama Tipe Input" />
                            <x-form.error name="name" />
                        </div>
                    </div>
                </x-row>
            </x-form>
        </x-slot>
        <x-slot name="footer">
            <x-button variant="primary" form="form-input-tipe-input-indikator" type="submit" wire:loading.attr="disabled" title="Simpan" />
        </x-slot>
    </x-modal>
</div>
