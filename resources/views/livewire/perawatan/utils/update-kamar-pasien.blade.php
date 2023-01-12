<div>
    @once
        @push('js')
            
        @endpush
    @endonce
    <x-modal :livewire="true" id="modal-update-kamar-pasien" title="Update Harga Kamar Pasien">
        <x-slot name="body">
            <x-row>
                <div class="col-4">
                    <div class="form-group">
                        <label class="text-sm" for="no_rawat">No. Rawat</label>
                        <input type="text" class="form-control form-control-sm" id="no_rawat" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label class="text-sm" for="kamar">Kamar</label>
                        <input type="text" class="form-control form-control-sm" id="kamar" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-5">
                    <div class="form-group">
                        <label class="text-sm" for="pasien">Pasien</label>
                        <input type="text" class="form-control form-control-sm" id="pasien" readonly autocomplete="off">
                    </div>
                </div>
            </x-row>
            <x-row>
                <div class="col-6">
                    <div class="form-group">
                        <label class="text-sm" for="harga_kamar">Harga Kamar Sebelumnya</label>
                        <input type="text" class="form-control form-control-sm" id="harga_kamar" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="text-sm" for="harga_kamar_baru">Harga Kamar Baru</label>
                        <input type="text" class="form-control form-control-sm" id="harga_kamar_baru" autocomplete="off">
                    </div>
                </div>
            </x-row>
        </x-slot>
        <x-slot name="footer" class="justify-content-end">
            <x-button class="btn-default" id="batal-simpan" title="Batal" />
            <x-button class="btn-primary" id="simpan-data" title="Simpan" icon="fas fa-save">
        </x-slot>
    </x-modal>
</div>
