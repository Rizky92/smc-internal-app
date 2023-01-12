<div>
    @once
        @push('js')
            
        @endpush
    @endonce
    <x-modal :livewire="true" id="modal-update-kamar-pasien" title="Update Harga Kamar Pasien">
        <x-slot name="body">

        </x-slot>
        <x-slot name="footer" class="justify-content-end">
            <x-button class="btn-default" id="batal-simpan" title="Batal" />
            <x-button class="btn-primary" id="simpan-data" title="Simpan" icon="fas fa-save">
        </x-slot>
    </x-modal>
</div>
