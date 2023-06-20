<div>
    <x-flash />

    <livewire:keuangan.r-k-a-t.modal.r-k-a-t-baru />

    @once
        @push('js')
            <script>
                function loadData(e) {
                    let { id, nama, deskripsi } = e.dataset

                    @this.emit('prepare', id, nama, deskripsi)

                    $('#modal-anggaran-baru').modal('show')
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
                <x-button variant="primary" size="sm" title="Anggaran Baru" icon="fas fa-plus" data-toggle="modal" data-target="#modal-anggaran-baru" class="btn-primary ml-3" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="min-width: 100%" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th style="width: 7ch" name="id" title="#" />
                    <x-table.th style="width: 30ch" name="nama" title="Nama Anggaran" />
                    <x-table.th name="deskripsi" title="Deskripsi" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataAnggaran as $item)
                        <x-table.tr>
                            <x-table.td
                                clickable
                                data-id="{{ $item->id }}"
                                data-nama="{{ $item->nama }}"
                                data-deskripsi="{{ $item->deskripsi }}"
                            >
                                {{ $item->id }}
                            </x-table.td>
                            <x-table.td>{{ $item->nama }}</x-table.td>
                            <x-table.td>{{ $item->deskripsi }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="3" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataAnggaran" />
        </x-slot>
    </x-card>
</div>
