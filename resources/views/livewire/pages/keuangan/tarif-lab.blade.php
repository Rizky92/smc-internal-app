<div>
    <x-flash />

    @can('keuangan.tarif-lab.create')
        <livewire:pages.keuangan.modal.import-tarif-lab />
    @endcan

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex class="mt-2">
                @can('keuangan.tarif-lab.create')
                    <x-button variant="primary" size="sm" title="Import" icon="fas fa-plus" data-toggle="modal" data-target="#modal-import-tarif-lab" class="btn-primary ml-auto" />
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
                    <x-table.th name="kd_jenis_prw" title="Kode Periksa" />
                    <x-table.th name="nm_perawatan" title="Nama Pemeriksaan" />
                    <x-table.th name="bagian_rs" title="Jasa Sarana" />
                    <x-table.th name="bhp" title="Paket BHP" />
                    <x-table.th name="tarif_perujuk" title="Jasa Medis Perujuk" />
                    <x-table.th name="tarif_tindakan_dokter" title="Jasa Medis Dokter" />
                    <x-table.th name="tarif_tindakan_petugas" title="Jasa Medis Petugas" />
                    <x-table.th name="kso" title="KSO" />
                    <x-table.th name="menejemen" title="Menejemen" />
                    <x-table.th name="total_byr" title="Total Tarif" />
                    <x-table.th name="png_jawab" title="Jenis Bayar" />
                    <x-table.th name="kelas" title="Kelas" />
                    <x-table.th name="kategori" title="Kategori" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collection as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->kd_jenis_prw }}</x-table.td>
                            <x-table.td>{{ $item->nm_perawatan }}</x-table.td>
                            <x-table.td>{{ rp($item->bagian_rs) }}</x-table.td>
                            <x-table.td>{{ rp($item->bhp) }}</x-table.td>
                            <x-table.td>{{ rp($item->tarif_perujuk) }}</x-table.td>
                            <x-table.td>{{ rp($item->tarif_tindakan_dokter) }}</x-table.td>
                            <x-table.td>{{ rp($item->tarif_tindakan_petugas) }}</x-table.td>
                            <x-table.td>{{ rp($item->kso) }}</x-table.td>
                            <x-table.td>{{ rp($item->menejemen) }}</x-table.td>
                            <x-table.td>{{ rp($item->total_byr) }}</x-table.td>
                            <x-table.td>{{ $item->png_jawab }}</x-table.td>
                            <x-table.td>{{ $item->kelas }}</x-table.td>
                            <x-table.td>{{ $item->kategori }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="13" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->collection" />
        </x-slot>
    </x-card>
</div>
