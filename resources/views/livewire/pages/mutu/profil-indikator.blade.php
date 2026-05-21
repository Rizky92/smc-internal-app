<div wire:init="loadProperties">
    <x-flash />

    @once
        @push('js')
            <script>
                function loadData(e) {
                    let { id } = e.dataset;
                    Livewire.emit('prepare', id);
                    $('#modal-input-profil-indikator').modal('show');
                }
            </script>
        @endpush
    @endonce

    <x-card>
        <x-slot name="header">
            <x-row-col-flex class="mt-2">
                <x-button variant="primary" size="sm" title="Tambah Profil Indikator" icon="fas fa-plus" class="ml-auto" wire:click="$emit('prepare')" />
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
                    <x-table.th name="id" title="ID" />
                    <x-table.th name="title" title="Judul Indikator" />
                    <x-table.th title="Kategori" />
                    <x-table.th title="Standar" />
                    <x-table.th title="Frekuensi" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($profiles as $profile)
                        <x-table.tr>
                            <x-table.td :clickable="true" data-id="{{ $profile->id }}">
                                {{ $profile->id }}
                            </x-table.td>
                            <x-table.td>{{ $profile->title }}</x-table.td>
                            <x-table.td>{{ $profile->category->name ?? '-' }}</x-table.td>
                            <x-table.td>{{ $profile->standard }}</x-table.td>
                            <x-table.td>{{ $profile->frequency }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="5" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$profiles" />
        </x-slot>
    </x-card>

    <livewire:pages.mutu.modal.input-profil-indikator />
</div>
