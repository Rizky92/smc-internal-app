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

                function pilihPasien(e) {
                    let noRkmMedis = e.dataset.noRkmMedis

                    @this.emit('epasien.pilihPasien', noRkmMedis)
                }
            </script>
        @endpush
    @endonce
    <x-modal title="Pilih Pasien" size="xl" id="modal-pilih-pasien" livewire centered>
        <x-slot name="body" class="p-0" style="overflow-x: hidden">
            <x-row-col class="pt-2">
                <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                    <x-slot name="columns">
                        <x-table.th name="no_rkm_medis" title="No. RM" />
                        <x-table.th name="nm_pasien" title="Nama Pasien" />
                        <x-table.th name="no_ktp" title="No. KTP" />
                        <x-table.th name="tgl_lahir" title="Tgl. Lahir" />
                        {{-- <x-table.th name="action" title="Aksi" class="text-center" /> --}}
                    </x-slot>
                    <x-slot name="body">                        
                        @forelse ($this->collection as $item)
                            <x-table.tr>
                                <x-table.td clickable funcName="pilihPasien" data-no-rkm-medis="{{ $item->no_rkm_medis }}" data-dismiss="modal">{{ $item->no_rkm_medis }}</x-table.td>
                                <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                                <x-table.td>{{ $item->no_ktp }}</x-table.td>
                                <x-table.td>{{ $item->tgl_lahir }}</x-table.td>
                            </x-table.tr>
                        @empty
                            <x-table.tr-empty colspan="4" padding />
                        @endforelse
                    </x-slot>
                </x-table>  
                <x-paginator class="px-4 py-3 bg-light border-top" :data="$this->collection" />
            </x-row-col>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-filter.search method="$refresh" />
        </x-slot>
    </x-modal>
</div>
