<div wire:init="loadProperties">
    <x-flash />

    @once
        @push('js')
            <script>
                function loadData(e) {
                    let { id, action } = e.dataset;

                    if (action === 'record') {
                        window.location.href = `{{ url('admin/mutu/indikator-mutu') }}/${id}`;
                    } else {
                        Livewire.emit('prepare', id);
                    }
                }
            </script>
        @endpush
    @endonce

    <x-card>
        <x-slot name="header">
            <x-row-col-flex class="mt-2">
                @can('mutu.indikator-mutu.create')
                    <x-filter.label constant-width>Unit :</x-filter.label>
                    <x-filter.select2 name="Unit" model="unitId" livewire :options="$this->unit" placeholder="SEMUA" />
                    <x-button variant="primary" size="sm" title="Tambah Mapping" icon="fas fa-plus" class="ml-auto" wire:click="$emit('prepare')" />
                @endcan

                <x-filter.button-export-excel class="ml-2" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2 mb-3">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th title="No. " />
                    <x-table.th title="Indikator" />
                    <x-table.th title="Unit" />
                    <x-table.th title="Standar" />
                    <x-table.th title="PJ" />
                    <x-table.th title="Status" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($indicators as $indicator)
                        <x-table.tr>
                            <x-table.td :clickable="true" data-id="{{ $indicator->id }}" data-action="record">
                                {{ $loop->iteration }}
                            </x-table.td>
                            <x-table.td>{{ $indicator->profile->title ?? '-' }}</x-table.td>
                            <x-table.td>{{ $indicator->unit->nama ?? '-' }}</x-table.td>
                            <x-table.td>{{ $indicator->profile->standard ?? '-' }}</x-table.td>
                            <x-table.td>{{ $indicator->person_in_charge }}</x-table.td>
                            <x-table.td>
                                <x-badge :variant="$indicator->status === 'active' ? 'success' : 'danger'">
                                    {{ $indicator->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </x-badge>
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="6" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$indicators" />
        </x-slot>
    </x-card>

    <livewire:pages.mutu.modal.input-indikator-mutu />
</div>
