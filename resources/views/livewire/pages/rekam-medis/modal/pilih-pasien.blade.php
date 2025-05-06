<div>
    @once
        @push('js')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    $('#modal-pilih-pasien').on('shown.bs.modal', e => {
                        @this.emit('epasien.show-pilih-pasien')
                    })

                    $('#modal-pilih-pasien').on('hide.bs.modal', e => {
                        @this.emit('epasien.hide-pilih-pasien')
                    })
                })
            </script>
        @endpush
    @endonce
    <x-modal title="Pilih Pasien" size="xl" id="modal-pilih-pasien" livewire centered dismisable="false">
        <x-slot name="body" class="p-0">
            <x-row-col class="pt-2">
                <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                    <x-slot name="columns">
                        <x-table.th name="no_rkm_medis" title="No. RM" />
                        <x-table.th name="nm_pasien" title="Nama Pasien" />
                        <x-table.th name="no_ktp" title="No. KTP" />
                        <x-table.th name="tgl_lahir" title="Tgl. Lahir" />
                    </x-slot>
                    <x-slot name="body">                        
                        @forelse ($this->collection as $item)
                            <x-table.tr>
                                <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                                <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                                <x-table.td>{{ $item->no_ktp }}</x-table.td>
                                <x-table.td>{{ $item->tgl_lahir }}</x-table.td>
                            </x-table.tr>
                        @empty
                            <x-table.tr-empty colspan="1" padding />
                        @endforelse
                    </x-slot>
                </x-table>  
            </x-row-col>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-filter.search method="$refresh" />
            {{-- <x-filter.toggle class="ml-1" id="show-checked-set-hak-akses" title="Tampilkan yang dipilih" model="showChecked" /> --}}
            {{-- <x-button size="sm" class="ml-auto" data-dismiss="modal" title="Batal" />
            <x-button size="sm" variant="primary" class="ml-2" data-dismiss="modal" wire:click="$emit('khanza.set')" title="Simpan" icon="fas fa-save" /> --}}
        </x-slot>
    </x-modal>
</div>
