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

    <x-modal id="modal-input-indikator-mutu" title="Input Indikator Mutu" size="xl" livewire>
        <x-slot name="body" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-input-indikator-mutu" wire:submit.prevent="save">
                <x-row>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Judul Indikator</label>
                            <input type="text" wire:model.defer="title" class="form-control form-control-sm" placeholder="Masukan Judul Indikator" />
                            <x-form.error name="title" />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Urutan</label>
                            <input type="number" wire:model.defer="sort_order" class="form-control form-control-sm" />
                            <x-form.error name="sort_order" />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Status</label>
                            <select wire:model.defer="status" class="form-control form-control-sm">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                            <x-form.error name="status" />
                        </div>
                    </div>
                </x-row>

                <x-row>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Unit / Bidang</label>
                            <x-form.select2 id="bidang-id" model="bidang_id" :options="$this->unit" placeholder="Pilih Unit" width="full-width" />
                            <x-form.error name="bidang_id" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Kategori Indikator</label>
                            <x-form.select2 id="category-id" model="quality_indicator_category_id" :options="$this->category" placeholder="Pilih Kategori" width="full-width" />
                            <x-form.error name="quality_indicator_category_id" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tipe Input</label>
                            <x-form.select2 id="input-type-id" model="quality_indicator_input_type_id" :options="$this->inputType" placeholder="Pilih Tipe Input" width="full-width" />
                            <x-form.error name="quality_indicator_input_type_id" />
                        </div>
                    </div>
                </x-row>

                <x-row>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Dimensi</label>
                            <input type="text" wire:model.defer="dimension" class="form-control form-control-sm" placeholder="Contoh: Keselamatan, Efisiensi, dll" />
                            <x-form.error name="dimension" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Frekuensi Pengumpulan Data</label>
                            <x-form.select id="frequency" model="frequency" :options="$this->frequencyOptions" placeholder="Pilih Frekuensi" width="full-width" />
                            <x-form.error name="frequency" />
                        </div>
                    </div>
                </x-row>

                <x-row>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tujuan</label>
                            <textarea wire:model.defer="objective" class="form-control form-control-sm" rows="2" placeholder="Masukan Tujuan Indikator"></textarea>
                            <x-form.error name="objective" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Definisi Operasional</label>
                            <textarea wire:model.defer="definition" class="form-control form-control-sm" rows="2" placeholder="Masukan Definisi Operasional"></textarea>
                            <x-form.error name="definition" />
                        </div>
                    </div>
                </x-row>

                <x-row>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Inklusi</label>
                            <textarea wire:model.defer="inclusion" class="form-control form-control-sm" rows="2" placeholder="Kriteria Inklusi"></textarea>
                            <x-form.error name="inclusion" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Eksklusi</label>
                            <textarea wire:model.defer="exclusion" class="form-control form-control-sm" rows="2" placeholder="Kriteria Eksklusi"></textarea>
                            <x-form.error name="exclusion" />
                        </div>
                    </div>
                </x-row>

                <x-row>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Numerator</label>
                            <textarea wire:model.defer="numerator" class="form-control form-control-sm" rows="2" placeholder="Deskripsi Pembilang"></textarea>
                            <x-form.error name="numerator" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Denominator</label>
                            <textarea wire:model.defer="denominator" class="form-control form-control-sm" rows="2" placeholder="Deskripsi Penyebut"></textarea>
                            <x-form.error name="denominator" />
                        </div>
                    </div>
                </x-row>

                <x-row>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Standar (%)</label>
                            <input type="text" wire:model.defer="standard" class="form-control form-control-sm" placeholder="Contoh: 100%" />
                            <x-form.error name="standard" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Periode Analisis (Bulan)</label>
                            <input type="number" wire:model.defer="analysis_period" class="form-control form-control-sm" placeholder="Contoh: 1" />
                            <x-form.error name="analysis_period" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Sumber Data</label>
                            <input type="text" wire:model.defer="data_source" class="form-control form-control-sm" placeholder="Contoh: Rekam Medis" />
                            <x-form.error name="data_source" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Penanggung Jawab</label>
                            <input type="text" wire:model.defer="person_in_charge" class="form-control form-control-sm" placeholder="Nama / Jabatan" />
                            <x-form.error name="person_in_charge" />
                        </div>
                    </div>
                </x-row>
            </x-form>
        </x-slot>
        <x-slot name="footer">
            <x-button variant="primary" form="form-input-indikator-mutu" type="submit" wire:loading.attr="disabled" icon="fas fa-save" title="Simpan" />
        </x-slot>
    </x-modal>
</div>
