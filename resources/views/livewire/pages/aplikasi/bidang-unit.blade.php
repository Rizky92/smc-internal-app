<div>
    <x-flash />

    <livewire:pages.aplikasi.modal.input-bidang-unit />

    @once
        @push('js')
            <script>
                function loadData(e) {
                    let {
                        bidangId,
                        parentId,
                        name
                    } = e.dataset

                    @this.emit('prepare', bidangId, parentId, name)

                    $('#modal-input-bidang-unit').modal('show')
                }
            </script>
        @endpush
    @endonce

    <x-card>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
                <x-button variant="primary" size="sm" title="Bidang Baru" icon="fas fa-plus" data-toggle="modal" data-target="#modal-input-bidang-unit" class="btn-primary ml-3" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="min-width: 100%" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th title="Nama" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->bidangUnit as $item)
                        <x-table.tr>
                            <x-table.td clickable data-bidang-id="{{ $item->id }}" data-parent-id="{{ $item->parent_id ?? -1 }}" data-name="{{ $item->nama }}">
                                {{ $item->nama }}
                            </x-table.td>
                        </x-table.tr>
                        @foreach ($item->descendants ?? [] as $descendant)
                            <x-table.tr>
                                <x-table.td clickable data-bidang-id="{{ $descendant->id }}" data-parent-id="{{ $descendant->parent_id ?? -1 }}" data-name="{{ $descendant->nama }}">
                                    @for ($i = 0; $i < $descendant->depth; $i++)
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                    @endfor

                                    {{ $descendant->nama }}
                                </x-table.td>
                            </x-table.tr>
                        @endforeach
                    @empty
                        <x-table.tr-empty colspan="1" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->bidangUnit" />
        </x-slot>
    </x-card>
</div>
