<div>
    @push('js')
        <script>
            window.addEventListener('input-focus-area.show-modal', (e) => {
                $('#modal-input-focus-area').modal('show');
            });

            window.addEventListener('input-focus-area.hide-modal', (e) => {
                $('#modal-input-focus-area').modal('hide');
            });

            $(document).on('focus-area-saved', () => {
                $('#modal-input-focus-area').modal('hide');
            });
        </script>
    @endpush

    <x-modal id="modal-input-focus-area" title="Input Fokus Area / Bab" size="lg" livewire>
        <x-slot name="body" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-input-focus-area" wire:submit.prevent="save">
                <x-row>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Kode</label>
                            <input type="text" wire:model.defer="kode" class="form-control form-control-sm" placeholder="Mis: TKRS" />
                            <x-form.error name="kode" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Urutan</label>
                            <input type="number" wire:model.defer="urutan" class="form-control form-control-sm" min="0" />
                            <x-form.error name="urutan" />
                        </div>
                    </div>
                </x-row>
                <x-row>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Nama Fokus Area</label>
                            <input type="text" wire:model.defer="nama" class="form-control form-control-sm" placeholder="Nama lengkap Fokus Area" />
                            <x-form.error name="nama" />
                        </div>
                    </div>
                </x-row>
                <x-row>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Deskripsi (opsional)</label>
                            <textarea wire:model.defer="deskripsi" class="form-control form-control-sm" rows="3" placeholder="Deskripsi Fokus Area"></textarea>
                            <x-form.error name="deskripsi" />
                        </div>
                    </div>
                </x-row>
            </x-form>
        </x-slot>
        <x-slot name="footer">
            <x-button variant="primary" form="form-input-focus-area" type="submit" wire:loading.attr="disabled" title="Simpan" />
        </x-slot>
    </x-modal>
</div>
