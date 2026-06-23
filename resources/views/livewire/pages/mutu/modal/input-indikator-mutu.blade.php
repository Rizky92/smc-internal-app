<div>
    @push('js')
        <script>
            window.addEventListener('input-indikator-mutu.show-modal', (e) => {
                $('#modal-input-indikator-mutu').modal('show');
            });

            window.addEventListener('input-indikator-mutu.hide-modal', (e) => {
                $('#modal-input-indikator-mutu').modal('hide');
            });

            $(document).on('indicator-saved', () => {
                $('#modal-input-indikator-mutu').modal('hide');
            });
        </script>
    @endpush

    <x-modal id="modal-input-indikator-mutu" title="Mapping Indikator Departemen" size="lg" livewire>
        <x-slot name="body" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-input-indikator-mutu" wire:submit.prevent="save">
                <x-row>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Pilih Profil Indikator</label>
                            <x-form.select2 id="quality_indicator_profile_id" model="quality_indicator_profile_id" :options="$this->profile" placeholder="-" width="full-width" />
                            <x-form.error name="quality_indicator_profile_id" />
                        </div>
                    </div>
                </x-row>

                <x-row>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Departemen</label>
                            <x-form.select2 id="dep_id" model="dep_id" :options="$this->departemen" placeholder="-" width="full-width" />
                            <x-form.error name="dep_id" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status</label>
                            <select wire:model.defer="status" class="custom-control custom-select text-sm">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                            <x-form.error name="status" />
                        </div>
                    </div>
                </x-row>

                <x-row>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Penanggung Jawab (di Departemen)</label>
                            <input type="text" wire:model.defer="person_in_charge" class="form-control form-control-sm" placeholder="Nama PJ Unit" />
                            <x-form.error name="person_in_charge" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Sumber Data</label>
                            <input type="text" wire:model.defer="data_source" class="form-control form-control-sm" placeholder="Contoh: Register Unit" />
                            <x-form.error name="data_source" />
                        </div>
                    </div>
                </x-row>
            </x-form>
        </x-slot>
        <x-slot name="footer">
            <x-button variant="primary" form="form-input-indikator-mutu" type="submit" wire:loading.attr="disabled" icon="fas fa-save" title="Simpan Mapping" />
        </x-slot>
    </x-modal>
</div>
