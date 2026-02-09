<div>
    <x-flash />

    @can('keuangan.tarif-ranap.create')
        <livewire:pages.keuangan.modal.import-tarif-ranap />
    @endcan

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex class="mt-2">
                @can('keuangan.tarif-ranap.create')
                    <x-button variant="primary" size="sm" title="Import" icon="fas fa-plus" data-toggle="modal" data-target="#modal-import-tarif-ranap" class="btn-primary ml-auto" />
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
                    <x-table.th name="nm_perawatan" title="Nama Tindakan" />
                    <x-table.th name="nm_kategori" title="Kategori" />
                    <x-table.th name="material" title="Jasa Sarana" />
                    <x-table.th name="bhp" title="BHP/Paket Obat" />
                    <x-table.th name="tarif_tindakandr" title="Jasa Medis Dr" />
                    <x-table.th name="tarif_tindakanpr" title="Jasa Medis PR" />
                    <x-table.th name="kso" title="KSO" />
                    <x-table.th name="menejemen" title="Menejemen" />
                    <x-table.th name="total_byrdr" title="Total Bayar DR" />
                    <x-table.th name="total_byrpr" title="Total Bayar PR" />
                    <x-table.th name="total_byrdrpr" title="Total Bayar DR & PR" />
                    <x-table.th name="kelas" title="Jenis Bayar" />
                    <x-table.th name="png_jawab" title="Nama Bangsal" />
                    <x-table.th name="nm_bangsal" title="Kelas" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collection as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->kd_jenis_prw }}</x-table.td>
                            <x-table.td>{{ $item->nm_perawatan }}</x-table.td>
                            <x-table.td>{{ $item->nm_kategori }}</x-table.td>
                            <x-table.td>{{ $item->material }}</x-table.td>
                            <x-table.td>{{ $item->bhp }}</x-table.td>
                            <x-table.td>{{ $item->tarif_tindakandr }}</x-table.td>
                            <x-table.td>{{ $item->tarif_tindakanpr }}</x-table.td>
                            <x-table.td>{{ $item->kso }}</x-table.td>
                            <x-table.td>{{ $item->menejemen }}</x-table.td>
                            <x-table.td>{{ $item->total_byrdr }}</x-table.td>
                            <x-table.td>{{ $item->total_byrpr }}</x-table.td>
                            <x-table.td>{{ $item->total_byrdrpr }}</x-table.td>
                            <x-table.td>{{ $item->kelas }}</x-table.td>
                            <x-table.td>{{ $item->png_jawab }}</x-table.td>
                            <x-table.td>{{ $item->nm_bangsal }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="15" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->collection" />
        </x-slot>
    </x-card>
</div>
