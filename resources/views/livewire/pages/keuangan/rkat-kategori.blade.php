<div>
    <x-flash />

    @can('keuangan.rkat-kategori.create')
        <livewire:pages.keuangan.modal.r-k-a-t-input-kategori />

        @once
            @push('js')
                <script>
                    function loadData(e) {
                        let {
                            id,
                            nama,
                            deskripsi
                        } = e.dataset

                        @this.emit('prepare', id, nama, deskripsi)

                        $('#modal-input-kategori-rkat').modal('show')
                    }
                </script>
            @endpush
        @endonce
    @endcan

    <x-card>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
                @can('keuangan.rkat-kategori.create')
                    <x-button variant="primary" size="sm" title="Anggaran Baru" icon="fas fa-plus" data-toggle="modal" data-target="#modal-input-kategori-rkat" class="btn-primary ml-3" />
                @endcan
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
                                :clickable="auth()
                                    ->user()
                                ->can('keuangan.rkat-kategori.update')"
                                data-id="{{ $item->id }}"
                                data-nama="{{ $item->nama }}"
                                data-deskripsi="{{ $item->deskripsi }}">
                                {{ $item->id }}
                            </x-table.td>
                            <x-table.td>{{ $item->nama }}</x-table.td>
                            <x-table.td>{{ $item->deskripsi }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="3" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataAnggaran" />
        </x-slot>
    </x-card>
</div>
