<div>
    <x-flash />

    @can('keuangan.posting-jurnal.create')
        <livewire:pages.keuangan.modal.input-posting-jurnal />
        
        @once
            @push('js')
                <script>
                    function loadData(e) {
                        let {
                            keterangan
                        } = e.dataset

                        @this.emit('prepare', {
                            keternangan
                        })

                        $('#modal-input-posting-jurnal').modal('show')
                    }
                </script>
            @endpush
        @endonce
    @endcan
    
    <x-card>
        <x-slot name="header">
            <x-row-col-flex class="pt-3 border-top">
                @can('keuangan.posting-jurnal.create')
                    <x-button variant="primary" size="sm" title="Jurnal Baru" icon="fas fa-plus" data-toggle="modal" data-target="#modal-input-posting-jurnal" class="btn-primary ml-auto" />
                @endcan
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
                
        </x-slot>
        <x-slot name="footer">
            {{-- <x-paginator :data="$this->collectionProperty" /> --}}
        </x-slot>
    </x-card>
</div>
