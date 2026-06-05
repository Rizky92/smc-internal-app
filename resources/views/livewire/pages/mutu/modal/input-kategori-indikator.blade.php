<div>
    @push('js')
        <script>
            window.addEventListener('input-kategori-indikator.show-modal', (e) => {
                $('#modal-input-kategori-indikator').modal('show');
            });

            window.addEventListener('input-kategori-indikator.hide-modal', (e) => {
                $('#modal-input-kategori-indikator').modal('hide');
            });

            $(document).on('category-saved', () => {
                $('#modal-input-kategori-indikator').modal('hide');
            });
        </script>
    @endpush

    <x-modal id="modal-input-kategori-indikator" title="Input Kategori Indikator" size="lg" livewire>
        <x-slot name="body" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-input-kategori-indikator" wire:submit.prevent="save">
                <x-row>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Nama Kategori</label>
                            <input type="text" wire:model.defer="name" class="form-control form-control-sm" placeholder="Masukan Nama Kategori" />
                            <x-form.error name="name" />
                        </div>
                    </div>
                </x-row>
            </x-form>
        </x-slot>
        <x-slot name="footer">
            <x-button variant="primary" form="form-input-kategori-indikator" type="submit" wire:loading.attr="disabled" title="Simpan" />
        </x-slot>
    </x-modal>
</div>
