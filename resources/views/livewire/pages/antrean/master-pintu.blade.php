<div wire:init="loadProperties">
    <x-flash />

    <livewire:pages.antrean.modal.input-master-pintu />

    @once
        @push('js')
            <script>
                function loadData(e) {
                    let { kodePintu, namaPintu } = e.dataset;

                    Livewire.emit('prepare', {
                        kodePintu,
                        namaPintu,
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
                    <x-button variant="primary" size="sm" title="Buat" icon="fas fa-plus" data-toggle="modal" data-target="#modal-input-pintu" class="btn-primary ml-auto" />
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
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->pintu as $pintu)
                        <x-table.tr>
                            <x-table.td :clickable="user()->can('antrean.manajemen-pintu.update')" data-kode-pintu="{{ $pintu->kd_pintu }}" data-nama-pintu="{{ $pintu->nm_pintu }}">
                                {{ $pintu->kd_pintu }}
                            </x-table.td>
                            <x-table.td>{{ $pintu->nm_pintu }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="2" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->pintu" />
        </x-slot>
    </x-card>
</div>
