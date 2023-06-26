<div>
    @push('js')
        <script>
            $('#modal-input-pelaporan-rkat').on('shown.bs.modal', e => {
                @this.emit('pelaporan-rkat.show-modal')
            })

            $('#modal-input-pelaporan-rkat').on('hide.bs.modal', e => {
                @this.emit('pelaporan-rkat.hide-modal')
            })

            $(document).on('data-saved', () => {
                $('#modal-input-pelaporan-rkat').modal('hide')
            })
        </script>
    @endpush
    <x-modal id="modal-input-pelaporan-rkat" :title="$this->isUpdating() ? 'Edit Data Penggunaan RKAT' : 'Input Data Penggunaan RKAT'" livewire centered>
        <x-slot name="body" class="p-0" style="overflow-x: hidden">
            <x-form id="form-input-pelaporan-rkat" livewire :submit="$this->isUpdating() ? 'update' : 'create'">
                <x-row-col class="sticky-top bg-white pt-3 pb-1 px-3">
                    <div class="form-group">
                        <label for="anggaran-bidang-id">Anggaran bidang digunakan:</label>
                        <x-form.select2
                            id="anggaran-bidang-id"
                            model="anggaranBidangId"
                            :options="$this->dataRKATPerBidang"
                            placeholder="-"
                            width="full-width"
                        />
                        <x-form.error name="anggaranBidangId" />
                    </div>
                    <div class="form-group mt-3">
                        <label for="tgl-pemakaian">Tgl. Pemakaian</label>
                        <x-form.date model="tglPakai" />
                        <x-form.error name="tglPakai" />
                    </div>
                    <div class="form-group mt-3">
                        <label for="nominal-pemakaian">Nominal Dipakai</label>
                        <input type="text" id="nominal-pemakaian" wire:model.defer="nominalPemakaian" class="form-control form-control-sm" />
                        <x-form.error name="nominalPemakaian" />
                    </div>
                    <div class="form-group mt-3">
                        <label for="keterangan">Keterangan</label>
                        <textarea id="keterangan" wire:model.defer="deskripsi" class="form-control form-control-sm"></textarea>
                        <x-form.error name="deskripsi" />
                    </div>
                </x-row-col>
            </x-form>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-button size="sm" class="ml-auto" data-dismiss="modal" id="batalsimpan" title="Batal" />
            <x-button size="sm" variant="primary" type="submit" class="ml-2" id="simpandata" title="Simpan" icon="fas fa-save" form="form-input-pelaporan-rkat" />
        </x-slot>
    </x-modal>
</div>
