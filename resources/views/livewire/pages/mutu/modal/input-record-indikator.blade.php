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
            <x-form wire:submit.prevent="save">
                <x-row>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" wire:model.defer="recordedDate" class="form-control form-control-sm" required />
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
            <x-button type="submit" variant="primary" wire:click="save">Simpan Penilaian</x-button>
        </x-slot>
    </x-modal>
</div>
