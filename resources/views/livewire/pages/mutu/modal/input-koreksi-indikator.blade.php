<div>
    @push('js')
        <script>
            window.addEventListener('open-modal', (e) => {
                $(`.modal#${e.detail.id}`).modal('show');
            });

            window.addEventListener('close-modal', (e) => {
                $(`.modal#${e.detail.id}`).modal('hide');
            });
        </script>
    @endpush

    <x-modal id="modal-input-koreksi-indikator" title="Koreksi Data Indikator" size="lg" livewire>
        <x-slot name="body" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-input-koreksi-indikator" wire:submit.prevent="save">
                <x-row>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Numerator</label>
                            <input type="number" wire:model.defer="numeratorValue" class="form-control form-control-sm" min="0" />
                            <x-form.error name="numeratorValue" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Denominator</label>
                            <input type="number" wire:model.defer="denominatorValue" class="form-control form-control-sm" min="0" />
                            <x-form.error name="denominatorValue" />
                        </div>
                    </div>
                </x-row>

                <x-row>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea wire:model.defer="notes" class="form-control form-control-sm" rows="2"></textarea>
                            <x-form.error name="notes" />
                        </div>
                    </div>
                </x-row>

                <x-row>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>
                                Alasan Koreksi
                                <span class="text-danger">*</span>
                            </label>
                            <textarea
                                wire:model.defer="reason"
                                class="form-control form-control-sm"
                                rows="2"
                                placeholder="Contoh: Salah input numerator, Terdapat data pasien duplikat, Kesalahan perhitungan denominator"></textarea>
                            <x-form.error name="reason" />
                            <small class="text-muted">Alasan koreksi akan dicatat pada audit trail.</small>
                        </div>
                    </div>
                </x-row>
            </x-form>
        </x-slot>
        <x-slot name="footer">
            <x-button variant="primary" form="form-input-koreksi-indikator" type="submit" wire:loading.attr="disabled" icon="fas fa-check" title="Simpan & Setujui" />
        </x-slot>
    </x-modal>
</div>
