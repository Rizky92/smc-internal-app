<div wire:init="loadProperties">
    <x-flash />

    <livewire:pages.aplikasi.modal.input-pintu />

    @once
        @push('js')
            <script>
                function loadData(e) {
                    let {
                        pintuId,
                        kodePintu,
                        namaPintu,
                        kodePoliklinik,
                        kodeDokter
                    } = e.dataset

                    @this.emit('prepare', {
                        pintuId,
                        kodePintu,
                        namaPintu,
                        kodePoliklinik,
                        kodeDokter
                    })

                    $('#modal-input-pintu').modal('show')
                }
            </script>
        @endpush
    @endonce

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex class="mt-2 mb-3">
                <x-button variant="primary" size="sm" title="Buat" icon="fas fa-plus" data-toggle="modal" data-target="#modal-input-pintu" class="btn-primary ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex>
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="width: 100%" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="kd_pintu" title="Kode Pintu" />
                    <x-table.th name="nm_pintu" title="Nama Pintu" />
                    <x-table.th>Poli</x-table.th>
                    <x-table.th>Dokter</x-table.th>
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->pintu as $pintu)
                        <x-table.tr>
                            <x-table.td
                                clickable
                                data-pintu-id="{{ $pintu->id }}"
                                data-kode-poliklinik="{{ $pintu->poli }}"
                                data-kode-dokter="{{ $pintu->dokter }}"
                                data-kode-pintu="{{ $pintu->kd_pintu }}"
                                data-nama-pintu="{{ $pintu->nm_pintu }}">
                                {{ $pintu->kd_pintu }}
                            </x-table.td>
                            <x-table.td>{{ $pintu->nm_pintu }}</x-table.td>
                            <x-table.td>
                                <div class="d-inline-flex flex-wrap" style="gap: 0.25rem">
                                    @foreach ($pintu->poliklinik as $poli)
                                        <x-badge variant="secondary">
                                            {{ $poli->nm_poli }}
                                        </x-badge>
                                    @endforeach
                                </div>
                            </x-table.td>
                            <x-table.td>
                                <div class="d-inline-flex flex-wrap" style="gap: 0.25rem">
                                    @foreach ($pintu->dokter as $dokter)
                                        <x-badge variant="secondary">
                                            {{ $dokter->nm_dokter }}
                                        </x-badge>
                                    @endforeach
                                </div>
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="4" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->pintu" />
        </x-slot>
    </x-card>
</div>
