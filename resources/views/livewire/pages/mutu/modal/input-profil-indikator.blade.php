<div>
    @push('js')
        <script>
            window.addEventListener('input-profil-indikator.show-modal', (e) => {
                $('#modal-input-profil-indikator').modal('show')

                setTimeout(() => {
                    $('.select2-profile').select2({
                        dropdownParent: $('#modal-input-profil-indikator'),
                        width: '100%',
                        theme: 'bootstrap4',
                        dropdownCssClass: 'text-sm px-0'
                    })
                }, 200)
            })

            window.addEventListener('input-profil-indikator.hide-modal', (e) => {
                $('#modal-input-profil-indikator').modal('hide')
            })

            $(document).ready(function() {
                $(document).on('change', '.select2-profile', function (e) {
                    @this.set($(this).attr('wire:model.defer'), $(this).val())
                })
            })

            $(document).on('profile-saved', () => {
                $('#modal-input-profil-indikator').modal('hide')
            })
        </script>
    @endpush

    <x-modal id="modal-input-profil-indikator" title="Profil Indikator Mutu" size="xl" livewire>
        <x-slot name="body" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-input-profil-indikator" wire:submit.prevent="save">
                <x-row>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Judul Indikator</label>
                            <input type="text" wire:model.defer="title" class="form-control form-control-sm" placeholder="Masukan Judul Indikator" />
                            <x-form.error name="title" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Kategori Indikator</label>
                            <div style="width: 100%" wire:ignore>
                                <select id="category-id-profile" wire:model.defer="quality_indicator_category_id" class="form-control form-control-sm select2-profile input-sm">
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($this->category as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <x-form.error name="quality_indicator_category_id" />
                        </div>
                    </div>
                </x-row>

                <x-row>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tipe Input</label>
                            <div style="width: 100%" wire:ignore>
                                <select id="input-type-id-profile" wire:model.defer="quality_indicator_input_type_id" class="form-control form-control-sm select2-profile input-sm">
                                    <option value="">Pilih Tipe Input</option>
                                    @foreach ($this->inputType as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <x-form.error name="quality_indicator_input_type_id" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Dimensi</label>
                            <input type="text" wire:model.defer="dimension" class="form-control form-control-sm" placeholder="Contoh: Keselamatan" />
                            <x-form.error name="dimension" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Frekuensi</label>
                            <x-form.select id="frequency-profile" model="frequency" :options="$this->frequencyOptions" placeholder="Pilih Frekuensi" width="full-width" />
                            <x-form.error name="frequency" />
                        </div>
                    </div>
                </x-row>

                <x-row>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tujuan</label>
                            <textarea wire:model.defer="objective" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Definisi Operasional</label>
                            <textarea wire:model.defer="definition" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </x-row>

                <x-row>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Inklusi</label>
                            <textarea wire:model.defer="inclusion" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Eksklusi</label>
                            <textarea wire:model.defer="exclusion" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </x-row>

                <x-row>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Numerator</label>
                            <textarea wire:model.defer="numerator" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Denominator</label>
                            <textarea wire:model.defer="denominator" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </x-row>

                <x-row>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Standar (%)</label>
                            <input type="text" wire:model.defer="standard" class="form-control form-control-sm" placeholder="Contoh: 100%" />
                            <x-form.error name="standard" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Periode Analisis (Bulan)</label>
                            <input type="number" wire:model.defer="analysis_period" class="form-control form-control-sm" />
                        </div>
                    </div>
                </x-row>
            </x-form>
        </x-slot>
        <x-slot name="footer">
            <x-button variant="primary" form="form-input-profil-indikator" type="submit" wire:loading.attr="disabled" icon="fas fa-save" title="Simpan Profil" />
        </x-slot>
    </x-modal>
</div>
