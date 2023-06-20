<div>
    <x-flash />

    <livewire:aplikasi.modal.bidang-unit-baru />

    @once
        @push('js')
            <script>
                function loadData(e) {
                    let { id, name } = e.dataset

                    @this.emit('prepare', id, name)

                    $('#modal-bidang-baru').modal('show')
                }
            </script>
        @endpush
    @endonce

    <x-card>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
                <x-button variant="primary" size="sm" title="Bidang Baru" icon="fas fa-plus" data-toggle="modal" data-target="#modal-bidang-baru" class="btn-primary ml-3" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="min-width: 100%" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th style="width: 7ch" name="id" title="#" />
                    <x-table.th name="nama" title="Nama" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->bidangUnit as $item)
                        <x-table.tr>
                            <x-table.td
                                clickable
                                data-id="{{ $item->id }}"
                                data-name="{{ $item->nama }}"
                            >{{ $item->id }}</x-table.td>
                            <x-table.td>{{ $item->nama }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="2" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->bidangUnit" />
        </x-slot>
    </x-card>
</div>
