<div>
    @push('js')
        <script>
            window.addEventListener('input-mapping-jabatan-unit.show-modal', (e) => {
                $('#modal-input-mapping-jabatan-unit').modal('show');

                setTimeout(() => {
                    $('.select2-mapping').select2({
                        dropdownParent: $('#modal-input-mapping-jabatan-unit'),
                        width: '100%',
                        theme: 'bootstrap4',
                        dropdownCssClass: 'text-sm px-0'
                    });
                }, 200);
            });

            window.addEventListener('input-mapping-jabatan-unit.hide-modal', (e) => {
                $('#modal-input-mapping-jabatan-unit').modal('hide');
            });

            $(document).ready(function() {
                $(document).on('change', '.select2-mapping', function(e) {
                    @this.set($(this).attr('wire:model.defer'), $(this).val());
                });
            });

            $(document).on('mapping-saved', () => {
                $('#modal-input-mapping-jabatan-unit').modal('hide');
            });
        </script>
    @endpush

    <x-modal id="modal-input-mapping-jabatan-unit" title="Mapping Jabatan Unit" size="lg" livewire>
        <x-slot name="body" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-input-mapping-jabatan-unit" wire:submit.prevent="save">
                <x-row>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Jabatan</label>
                            <div style="width: 100%" wire:ignore>
                                <select wire:model.defer="jabatan_id" class="form-control form-control-sm select2-mapping input-sm">
                                    <option value="">Pilih Jabatan</option>
                                    @foreach ($this->jabatanOptions as $kd => $nm)
                                        <option value="{{ $kd }}">{{ $nm }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <x-form.error name="jabatan_id" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Unit / Bidang</label>
                            <div style="width: 100%" wire:ignore>
                                <select wire:model.defer="unit_ids" class="form-control form-control-sm select2-mapping input-sm" multiple>
                                    @foreach ($this->unitOptions as $id => $nama)
                                        <option value="{{ $id }}">{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <x-form.error name="unit_ids" />
                        </div>
                    </div>
                </x-row>
            </x-form>
        </x-slot>
        <x-slot name="footer">
            <x-button variant="primary" form="form-input-mapping-jabatan-unit" type="submit" wire:loading.attr="disabled" title="Simpan" />
        </x-slot>
    </x-modal>
</div>
