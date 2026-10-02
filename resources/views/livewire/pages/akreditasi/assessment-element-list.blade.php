<div wire:init="loadProperties">
    <x-flash />

    @once
        @push('js')
            <script>
                function loadData(e) {
                    let { id } = e.dataset;

                    Livewire.emit('prepare', id);

                    $('#modal-input-assessment-element').modal('show');
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
                <div class="ml-2" style="min-width: 16rem">
                    <x-filter.select name="searchStandard" :options="$this->standards" placeholder="SEMUA STANDAR" width="16rem" />
                </div>
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="standard_id" title="Standar" />
                    <x-table.th name="kode" title="Kode EP" />
                    <x-table.th name="deskripsi" title="Elemen Penilaian" />
                    <x-table.th name="proof_method_id" title="Metode" />
                    <x-table.th name="urutan" title="Urutan" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($elements as $element)
                        <x-table.tr>
                            <x-table.td :clickable="user()->can('akreditasi.master-data.update')" data-id="{{ $element->id }}">
                                [{{ $element->standard->focusArea->kode ?? '?' }}] {{ $element->standard->kode ?? '-' }}
                            </x-table.td>
                            <x-table.td>{{ $element->kode }}</x-table.td>
                            <x-table.td style="max-width: 400px">
                                <div class="text-truncate" style="max-width: 400px" title="{{ $element->deskripsi }}">
                                    {{ $element->deskripsi }}
                                </div>
                            </x-table.td>
                            <x-table.td>
                                @if ($element->proofMethod)
                                    <x-badge variant="info">{{ $element->proofMethod->kode }}</x-badge>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </x-table.td>
                            <x-table.td class="text-center">{{ $element->urutan }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="5" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$elements" />
        </x-slot>
    </x-card>

    <livewire:pages.akreditasi.modal.input-assessment-element />
</div>
