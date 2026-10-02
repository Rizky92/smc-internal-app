<div wire:init="loadProperties">
    <x-flash />

    @once
        @push('js')
            <script>
                function loadData(e) {
                    let { id } = e.dataset;

                    Livewire.emit('prepare', id);

                    $('#modal-input-proof-method').modal('show');
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
                    <x-table.th name="nama" title="Nama Metode Pembuktian" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($proofMethods as $proofMethod)
                        <x-table.tr>
                            <x-table.td :clickable="user()->can('akreditasi.master-data.update')" data-id="{{ $proofMethod->id }}">
                                {{ $proofMethod->kode }}
                            </x-table.td>
                            <x-table.td>{{ $proofMethod->nama }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="2" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$proofMethods" />
        </x-slot>
    </x-card>

    <livewire:pages.akreditasi.modal.input-proof-method />
</div>
