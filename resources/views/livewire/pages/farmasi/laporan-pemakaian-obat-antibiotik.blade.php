<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.label constant-width>Jenis Perawatan:</x-filter.label>
                <x-filter.select
                    model="jenisPerawatan"
                    :options="[
                        'semua' => 'Semua',
                        'Ralan' => 'Rawat Jalan',
                        'Ranap' => 'Rawat Inap',
                    ]" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="no_rawat" title="No. Rawat" />
                    <x-table.th name="no_rkm_medis" title="No. RM" />
                    <x-table.th name="nm_pasien" title="Nama Pasien" />
                    <x-table.th name="tgl_perawatan" title="Tanggal Pemakaian" />
                    <x-table.th name="kode_brng" title="Kode Obat" />
                    <x-table.th name="nama_brng" title="Nama Obat" />
                    <x-table.th name="jml" align="right" title="Jumlah" />
                    <x-table.th name="status_layanan" title="Jenis Perawatan" />
                    <x-table.th name="dokter" title="Dokter" />
                    <x-table.th name="nm_spesialis" title="Spesialis" />

                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataLaporanPemakaianObatAntibiotik as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->tgl_perawatan }}</x-table.td>
                            <x-table.td>{{ $item->kode_brng }}</x-table.td>
                            <x-table.td>{{ $item->nama_brng }}</x-table.td>
                            <x-table.td class="text-right">{{ round($item->jml, 2) }}</x-table.td>
                            <x-table.td>{{ $item->status_layanan }}</x-table.td>
                            <x-table.td>{{ $item->dokter }}</x-table.td>
                            <x-table.td>{{ $item->nm_sps }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="10" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataLaporanPemakaianObatAntibiotik" />
        </x-slot>
    </x-card>
</div>
