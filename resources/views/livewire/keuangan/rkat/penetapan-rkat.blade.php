<div>
    <x-flash />

    <livewire:keuangan.r-k-a-t.modal.input-penetapan-r-k-a-t />

    @once
        @push('js')
            <script>
                function loadData(e) {
                    let { anggaranBidangId } = e.dataset

                    @this.emit('prepare', anggaranBidangId)

                    $('.modal#modal-input-penetapan-rkat').modal('show')
                }
            </script>
        @endpush
    @endonce

    <x-card>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.label constant-width>Tahun:</x-filter.label>
                <x-filter.select model="tahun" :options="$this->dataTahun" />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2 mb-3">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
            <x-row-col-flex class="pt-3 border-top">
                <x-button variant="primary" size="sm" title="Anggaran Baru" icon="fas fa-plus" data-toggle="modal" data-target="#modal-input-penetapan-rkat" class="btn-primary ml-auto" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="min-width: 100%" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="tahun" title="Tahun" />
                    <x-table.th name="bidang_id" title="Bidang" />
                    <x-table.th name="anggaran_id" title="Anggaran" />
                    <x-table.th name="nominal_anggaran" title="Nominal" align="right" colspan="2" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataAnggaranBidang as $item)
                        <x-table.tr>
                            <x-table.td
                                clickable
                                data-id="{{ $item->id }}"
                            >
                                {{ $item->tahun }}
                            </x-table.td>
                            <x-table.td>{{ $item->bidang->nama }}</x-table.td>
                            <x-table.td>{{ $item->anggaran->nama }}</x-table.td>
                            <x-table.td-currency :value="$item->nominal_anggaran" />
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="5" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataAnggaranBidang" />
        </x-slot>
    </x-card>
</div>
