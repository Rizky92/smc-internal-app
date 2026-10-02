<div wire:init="loadProperties">
    <x-flash />

    @once
        @push('js')
            <script>
                function loadData(e) {
                    let { id } = e.dataset;

                    Livewire.emit('prepare', id);

                    $('#modal-input-standard').modal('show');
                }
            </script>
        @endpush
    @endonce

    <x-card>
        <x-slot name="header">
            <x-row-col-flex class="mt-2">
                <x-button variant="primary" size="sm" title="Tambah" icon="fas fa-plus" class="ml-auto" wire:click="$emit('prepare')" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2 mb-3">
                <x-filter.select-perpage />
                <div class="ml-2" style="min-width: 14rem">
                    <x-filter.select name="searchFocusArea" :options="$this->focusAreas" placeholder="SEMUA FOKUS AREA" width="14rem" />
                </div>
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="focus_area_id" title="Fokus Area" />
                    <x-table.th name="kode" title="Kode" />
                    <x-table.th name="judul" title="Judul Standar" />
                    <x-table.th name="urutan" title="Urutan" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($standards as $standard)
                        <x-table.tr>
                            <x-table.td :clickable="user()->can('akreditasi.master-data.update')" data-id="{{ $standard->id }}">
                                {{ $standard->focusArea->nama ?? '-' }}
                            </x-table.td>
                            <x-table.td>{{ $standard->kode }}</x-table.td>
                            <x-table.td>{{ $standard->judul }}</x-table.td>
                            <x-table.td class="text-center">{{ $standard->urutan }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="4" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$standards" />
        </x-slot>
    </x-card>

    <livewire:pages.akreditasi.modal.input-standard />
</div>
