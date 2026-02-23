<div wire:init="loadProperties">
    <x-flash />

    <livewire:pages.aplikasi.modal.input-pintu />

    @once
        @push('js')
            <script>
                function loadData(e) {
                    let { kodePintu, kodePoliklinik, kodeDokter } = e.dataset;

                    Livewire.emit('prepare', {
                        kodePintu,
                        kodePoliklinik,
                        kodeDokter,
                    });

                    $('#modal-input-pintu').modal('show');
                }
            </script>
        @endpush
    @endonce

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.select-perpage />
                @can('antrean.manajemen-pintu.create')
                    {{-- Mark this trigger as a create action so the modal can decide whether to clear client-side widgets --}}
                    <x-button variant="primary" size="sm" title="Buat" icon="fas fa-plus" data-toggle="modal" data-target="#modal-input-pintu" data-action="create" class="btn-primary ml-auto" />
                @endcan
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="width: 100%" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="kd_pintu" title="Kode Pintu" />
                    <x-table.th name="nm_pintu" title="Nama Pintu" />
                    <x-table.th title="Jadwal" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->pintu as $pintu)
                        <x-table.tr>
                            <x-table.td
                                :clickable="user()->can('antrean.manajemen-pintu.update')"
                                data-kode-poliklinik="{{ $pintu->kd_poli }}"
                                data-kode-dokter="{{ $pintu->kd_dokter }}"
                                data-kode-pintu="{{ $pintu->kd_pintu }}">
                                {{ $pintu->kd_pintu }}
                            </x-table.td>
                            <x-table.td>{{ $pintu->nm_pintu }}</x-table.td>
                            <x-table.td>
                                @if (! empty($pintu->jadwal) && $pintu->jadwal->isNotEmpty())
                                    <ul class="mb-0 pl-3">
                                        @foreach ($pintu->jadwal as $j)
                                            <li>{{ $j->nm_dokter }} - {{ $j->nm_poli }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="3" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->pintu" />
        </x-slot>
    </x-card>
</div>
