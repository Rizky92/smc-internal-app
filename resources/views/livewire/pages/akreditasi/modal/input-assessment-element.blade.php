<div>
    @push('js')
        <script>
            window.addEventListener('input-assessment-element.show-modal', (e) => {
                $('#modal-input-assessment-element').modal('show');
            });

            window.addEventListener('input-assessment-element.hide-modal', (e) => {
                $('#modal-input-assessment-element').modal('hide');
            });

            $(document).on('assessment-element-saved', () => {
                $('#modal-input-assessment-element').modal('hide');
            });
        </script>
    @endpush

    <x-modal id="modal-input-assessment-element" title="Input Elemen Penilaian" size="xl" livewire>
        <x-slot name="body" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-input-assessment-element" wire:submit.prevent="save">
                <x-row>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Standar</label>
                            <x-form.select model="standardId" :options="$this->standards" placeholder="Pilih Standar" />
                            <x-form.error name="standardId" />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Kode EP</label>
                            <input type="text" wire:model.defer="kode" class="form-control form-control-sm" placeholder="Mis: a" />
                            <x-form.error name="kode" />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Metode Pembuktian</label>
                            <x-form.select model="proofMethodId" :options="$this->proofMethods" placeholder="-" />
                            <x-form.error name="proofMethodId" />
                        </div>
                    </div>
                    <div class="col-md-2">
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
                            <label>Elemen Penilaian</label>
                            <textarea wire:model.defer="deskripsi" class="form-control form-control-sm" rows="3" placeholder="Deskripsi Elemen Penilaian"></textarea>
                            <x-form.error name="deskripsi" />
                        </div>
                    </div>
                </x-row>
                <x-row>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Penjelasan Kelengkapan Bukti (opsional)</label>
                            <textarea wire:model.defer="penjelasanKelengkapanBukti" class="form-control form-control-sm" rows="2" placeholder="Penjelasan tentang bukti yang diperlukan"></textarea>
                            <x-form.error name="penjelasanKelengkapanBukti" />
                        </div>
                    </div>
                </x-row>
            </x-form>
        </x-slot>
        <x-slot name="footer">
            <x-button variant="primary" form="form-input-assessment-element" type="submit" wire:loading.attr="disabled" title="Simpan" />
        </x-slot>
    </x-modal>
</div>
