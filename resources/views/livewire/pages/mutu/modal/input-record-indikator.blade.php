<div>
    @push('js')
        <script>
            window.addEventListener('open-modal', (e) => {
                $(`.modal#${e.detail.id}`).modal('show');
            });

            window.addEventListener('close-modal', (e) => {
                $(`.modal#${e.detail.id}`).modal('hide');
            });

            $(document).on('record-saved', () => {
                $('#modal-input-record-indikator').modal('hide');
            });
        </script>
    @endpush

    <x-modal id="modal-input-record-indikator" title="Input Penilaian Harian: {{ $indicatorName }}" size="lg" livewire>
        <x-slot name="body" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-input-record-indikator" wire:submit.prevent="save">
                <x-row>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" wire:model.defer="recordedDate" class="form-control form-control-sm" required {{ $isEdit ? 'disabled' : '' }} />
                            <x-form.error name="recordedDate" />
                        </div>
                    </div>
                </x-row>
                <x-row>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Numerator (Pembilang)</label>
                            <input type="number" wire:model.defer="numeratorValue" class="form-control form-control-sm" required />
                            <x-form.error name="numeratorValue" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Denominator (Penyebut)</label>
                            <input type="number" wire:model.defer="denominatorValue" class="form-control form-control-sm" required />
                            <x-form.error name="denominatorValue" />
                        </div>
                    </div>
                </x-row>
                <x-row>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Catatan / Keterangan</label>
                            <textarea wire:model.defer="notes" class="form-control form-control-sm" rows="3"></textarea>
                            <x-form.error name="notes" />
                        </div>
                    </div>
                </x-row>
            </x-form>
        </x-slot>
        <x-slot name="footer">
            <div class="d-flex justify-content-between w-100">
                <div>
                    @if ($isEdit)
                        <x-button
                            variant="danger"
                            title="Hapus"
                            icon="fas fa-trash"
                            onclick="confirm('Yakin ingin menghapus penilaian ini?') || event.stopImmediatePropagation()"
                            wire:click="delete" />
                    @endif
                </div>
                <div>
                    <x-button variant="primary" form="form-input-record-indikator" type="submit" wire:loading.attr="disabled" icon="fas fa-save" title="Simpan Penilaian" />
                </div>
            </div>
        </x-slot>
    </x-modal>
</div>
