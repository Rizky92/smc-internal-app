<div>
    @push('js')
        <script>
            window.addEventListener('input-standard.show-modal', (e) => {
                $('#modal-input-standard').modal('show');
            });

            window.addEventListener('input-standard.hide-modal', (e) => {
                $('#modal-input-standard').modal('hide');
            });

            $(document).on('standard-saved', () => {
                $('#modal-input-standard').modal('hide');
            });
        </script>
    @endpush

    <x-modal id="modal-input-standard" title="Input Standar Akreditasi" size="xl" livewire>
        <x-slot name="body" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-input-standard" wire:submit.prevent="save">
                <x-row>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Fokus Area</label>
                            <x-form.select model="focusAreaId" :options="$this->focusAreas" placeholder="Pilih Fokus Area" />
                            <x-form.error name="focusAreaId" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Kode Standar</label>
                            <input type="text" wire:model.defer="kode" class="form-control form-control-sm" placeholder="Mis: TKRS.1" />
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
                            <label>Judul Standar</label>
                            <input type="text" wire:model.defer="judul" class="form-control form-control-sm" placeholder="Judul Standar" />
                            <x-form.error name="judul" />
                        </div>
                    </div>
                </x-row>
                <x-row>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Maksud & Tujuan (opsional)</label>
                            <textarea wire:model.defer="maksudTujuan" class="form-control form-control-sm" rows="4" placeholder="Maksud dan tujuan standar"></textarea>
                            <x-form.error name="maksudTujuan" />
                        </div>
                    </div>
                </x-row>
            </x-form>
        </x-slot>
        <x-slot name="footer">
            <x-button variant="primary" form="form-input-standard" type="submit" wire:loading.attr="disabled" title="Simpan" />
        </x-slot>
    </x-modal>
</div>
