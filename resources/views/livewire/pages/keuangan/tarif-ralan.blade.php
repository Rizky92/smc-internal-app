<div>
    <x-flash />

    @can('keuangan.tarif-ralan.create')
        <livewire:pages.keuangan.modal.import-tarif-ralan />
    @endcan

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex class="mt-2">
                @can('keuangan.tarif-ralan.create')
                    <x-button variant="primary" size="sm" title="Import" icon="fas fa-plus" data-toggle="modal" data-target="#modal-import-tarif-ralan" class="btn-primary ml-auto" />
                @endcan

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
                    <x-table.th name="kd_jenis_prw" title="Kode Tindakan" />
                    <x-table.th name="nm_perawatan" title="Nama Tnd/Prw/Tagihan" />
                    <x-table.th name="nm_kategori" title="Kategori" />
                    <x-table.th name="material" title="Jasa Sarana" />
                    <x-table.th name="bhp" title="BHP/Paket Obat" />
                    <x-table.th name="tarif_tindakandr" title="Jasa Medis Dr" />
                    <x-table.th name="tarif_tindakanpr" title="Jasa Medis Pr" />
                    <x-table.th name="kso" title="KSO" />
                    <x-table.th name="menejemen" title="Menejemen" />
                    <x-table.th name="total_byrdr" title="Ttl Biaya Dr" />
                    <x-table.th name="total_byrpr" title="Ttl Biaya Pr" />
                    <x-table.th name="total_byrdrpr" title="Ttl Biaya Dr & Pr" />
                    <x-table.th name="png_jawab" title="Jenis Bayar" />
                    <x-table.th name="nm_poli" title="Poli" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collection as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->kd_jenis_prw }}</x-table.td>
                            <x-table.td>{{ $item->nm_perawatan }}</x-table.td>
                            <x-table.td>{{ $item->nm_kategori }}</x-table.td>
                            <x-table.td>{{ rp($item->material) }}</x-table.td>
                            <x-table.td>{{ rp($item->bhp) }}</x-table.td>
                            <x-table.td>{{ rp($item->tarif_tindakandr) }}</x-table.td>
                            <x-table.td>{{ rp($item->tarif_tindakanpr) }}</x-table.td>
                            <x-table.td>{{ rp($item->kso) }}</x-table.td>
                            <x-table.td>{{ rp($item->menejemen) }}</x-table.td>
                            <x-table.td>{{ rp($item->total_byrdr) }}</x-table.td>
                            <x-table.td>{{ rp($item->total_byrpr) }}</x-table.td>
                            <x-table.td>{{ rp($item->total_byrdrpr) }}</x-table.td>
                            <x-table.td>{{ $item->png_jawab }}</x-table.td>
                            <x-table.td>{{ $item->nm_poli }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="14" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->collection" />
        </x-slot>
    </x-card>
</div>
