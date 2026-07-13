<div>
@push('js')
    <script>
        $('#modal-verifikasi-pasien').on('shown.bs.modal', e => {
            @this.emit('verifikasi-pasien.show-modal')
        })

        $('#modal-verifikasi-pasien').on('hide.bs.modal', e => {
            @this.emit('verifikasi-pasien.hide-modal')
        })

        $(document).on('data-saved', () => {
            $('#modal-verifikasi-pasien').modal('hide')
        })
    </script>
@endpush

    <x-modal id="modal-verifikasi-pasien" title="Verifikasi Data Pasien" livewire centered static dismisable="false">
        <x-slot name="body" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-verifikasi-pasien" livewire :submit="$this->isUpdating() ? 'update' : 'create'">
                <x-row-col class="sticky-top bg-white">
                    <div class="form-group mt-3">
                        <label for="no-ktp">No KTP</label>
                        <input type="text" id="no-ktp" wire:model.defer="noKtp"
                            class="form-control form-control-sm" />
                        <x-form.error name="noKtp" />
                    </div>
                    <div class="form-group mt-3">
                        <label for="name">Nama</label>
                        <input type="text" id="name" wire:model.defer="name"
                            class="form-control form-control-sm" />
                        <x-form.error name="name" />
                    </div>
                    <div class="form-group mt-3">
                        <label for="tgl-lahir">Tanggal Lahir</label>
                        <x-form.date model="tglLahir" />
                        <x-form.error name="tglLahir" />
                    </div>
                </x-row-col>
            </x-form>
        </x-slot>
        <x-slot name="footer" class="d-flex justify-content-end">
            <x-button size="sm" variant="success" id="verifikasi" title="Verifikasi" icon="fas fa-check" wire:click="verifikasi" />
            <x-button size="sm" class="ml-auto" data-dismiss="modal" id="batalsimpan" title="Batal" />
            <x-button size="sm" variant="primary" type="submit" class="ml-2" id="simpandata" title="Simpan" icon="fas fa-save" form="form-verifikasi-pasien" />
        </x-slot>       
    </x-modal>
</div>
