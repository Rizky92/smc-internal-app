<div wire:init="loadProperties">
    <x-flash />

    @once
        @push('js')
            <script>
                function loadData(e) {
                    let { id } = e.dataset;

                    Livewire.emit('prepare', id);

                    $('#modal-input-focus-area').modal('show');
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
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="kode" title="Kode" />
                    <x-table.th name="nama" title="Nama Fokus Area / Bab" />
                    <x-table.th name="urutan" title="Urutan" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($focusAreas as $focusArea)
                        <x-table.tr>
                            <x-table.td :clickable="user()->can('akreditasi.master-data.update')" data-id="{{ $focusArea->id }}">
                                {{ $focusArea->kode }}
                            </x-table.td>
                            <x-table.td>{{ $focusArea->nama }}</x-table.td>
                            <x-table.td class="text-center">{{ $focusArea->urutan }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="3" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$focusAreas" />
        </x-slot>
    </x-card>

    <livewire:pages.akreditasi.modal.input-focus-area />
</div>
