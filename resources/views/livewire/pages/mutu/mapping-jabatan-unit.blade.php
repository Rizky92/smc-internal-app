<div wire:init="loadProperties">
    <x-flash />

    @once
        @push('js')
            <script>
                function loadData(e) {
                    let { id } = e.dataset;
                    Livewire.emit('prepare', id);
                    $('#modal-input-mapping-jabatan-unit').modal('show');
                }
            </script>
        @endpush
    @endonce

    <x-card>
        <x-slot name="header">
            <x-row-col-flex class="mt-2">
                <x-button variant="primary" size="sm" title="Tambah Mapping" icon="fas fa-plus" class="ml-auto" wire:click="$emit('prepare')" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2 mb-3">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="id" title="ID" />
                    <x-table.th title="Jabatan" />
                    <x-table.th title="Unit" />
                    <x-table.th title="Aksi" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($mappings as $mapping)
                        <x-table.tr>
                            <x-table.td>{{ $mapping->id }}</x-table.td>
                            <x-table.td>{{ $this->jabatanList->get($mapping->jabatan_id)->nm_jbtn ?? $mapping->jabatan_id }}</x-table.td>
                            <x-table.td>{{ $mapping->unit->nama ?? '-' }}</x-table.td>
                            <x-table.td>
                                <x-button
                                    variant="danger"
                                    size="xs"
                                    icon="fas fa-trash"
                                    title="Hapus"
                                    onclick="confirm('Yakin ingin menghapus mapping ini?') || event.stopImmediatePropagation()"
                                    wire:click="delete({{ $mapping->id }})" />
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="4" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$mappings" />
        </x-slot>
    </x-card>

    <livewire:pages.mutu.modal.input-mapping-jabatan-unit />
</div>
